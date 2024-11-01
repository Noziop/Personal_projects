from sqlalchemy.orm import Session
from sqlalchemy.exc import IntegrityError
from fastapi import HTTPException, status
from typing import Optional, List
from datetime import datetime
import bcrypt

from ..models.users import User, UserRole
from ..models.students import Student
from ..models.cohorts import Cohort
from ..models.student_cohort_history import StudentCohortHistory
from ..models.unavailability import StudentUnavailability
from ..models.public_holidays import PublicHoliday

class UserService:
    @staticmethod
    def authenticate_user(db: Session, email: str, password: str) -> Optional[User]:
        """Authentifie un utilisateur"""
        user = db.query(User).filter(User.email == email).first()
        
        if not user:
            return None
            
        if not bcrypt.checkpw(password.encode('utf-8'), 
                            user.password_hash.encode('utf-8')):
            return None

        # Mise à jour du last_login
        user.last_login = datetime.utcnow()
        db.commit()
            
        return user

    @staticmethod
    def create_staff(   
        db: Session,
        admin_id: int,
        email: str,
        password: str,
        first_name: str,
        last_name: str,
        slack_id: Optional[str] = None
    ) -> User:
        """Création de staff - réservé aux admins"""
        admin = db.query(User).filter(User.id == admin_id, 
                                    User.role == UserRole.ADMIN).first()
        if not admin:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Only admins can create staff members"
            )

        # Hash du mot de passe
        salt = bcrypt.gensalt()
        hashed_password = bcrypt.hashpw(password.encode('utf-8'), salt)
        
        try:
            staff = User(
                email=email,
                password_hash=hashed_password.decode('utf-8'),
                role=UserRole.STAFF,
                first_name=first_name,
                last_name=last_name,
                slack_id=slack_id
            )
            db.add(staff)
            db.commit()
            db.refresh(staff)
            return staff
            
        except IntegrityError:
            db.rollback()
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Email already registered"
            )

    @staticmethod
    def create_student(
        db: Session,
        creator_id: int,
        email: str,
        password: str,
        first_name: str,
        last_name: str,
        cohort_id: int,
        slack_id: Optional[str] = None
    ) -> User:
        """Création d'étudiant - réservé aux staff/admin"""
        creator = db.query(User).filter(
            User.id == creator_id,
            User.role.in_([UserRole.ADMIN, UserRole.STAFF])
        ).first()
        if not creator:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Only staff/admin can create students"
            )

        # Hash du mot de passe
        salt = bcrypt.gensalt()
        hashed_password = bcrypt.hashpw(password.encode('utf-8'), salt)
        
        try:
            # Création de l'utilisateur
            user = User(
                email=email,
                password_hash=hashed_password.decode('utf-8'),
                role=UserRole.STUDENT,
                first_name=first_name,
                last_name=last_name,
                slack_id=slack_id
            )
            db.add(user)
            db.commit()
            db.refresh(user)

            # Création du profil étudiant
            student = Student(
                user_id=user.id,
                current_cohort_id=cohort_id
            )
            db.add(student)
            db.commit()
            db.refresh(student)

            return user
            
        except IntegrityError:
            db.rollback()
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Email already registered"
            )

    @staticmethod
    def get_students_by_cohort(
        db: Session,
        cohort_id: int,
        requester_id: int
    ) -> List[Student]:
        """Liste des étudiants d'une cohorte - staff/admin only"""
        requester = db.query(User).filter(
            User.id == requester_id,
            User.role.in_([UserRole.ADMIN, UserRole.STAFF])
        ).first()
        if not requester:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Only staff/admin can view all students"
            )
        
        return db.query(Student).filter(
            Student.current_cohort_id == cohort_id
        ).all()

    @staticmethod
    def get_student_profile(
        db: Session,
        student_id: int,
        requester_id: int
    ) -> Student:
        """
        Récupère le profil d'un étudiant
        - Staff/admin peuvent voir tous les profils
        - Les étudiants ne peuvent voir que leur propre profil
        """
        requester = db.query(User).filter(User.id == requester_id).first()
        if not requester:
            raise HTTPException(status_code=404, detail="User not found")

        if requester.role == UserRole.STUDENT and requester_id != student_id:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Students can only view their own profile"
            )

        student = db.query(Student).filter(Student.id == student_id).first()
        if not student:
            raise HTTPException(status_code=404, detail="Student not found")
            
        return student

    @staticmethod
    def update_user(
        db: Session,
        user_id: int,
        requester_id: int,
        **kwargs
    ) -> User:
        """
        Mise à jour utilisateur avec vérification des permissions :
        - Admin peut tout modifier
        - Staff peut modifier étudiants
        - Étudiant peut modifier certains de ses propres champs
        """
        requester = db.query(User).filter(User.id == requester_id).first()
        target = db.query(User).filter(User.id == user_id).first()

        if not requester or not target:
            raise HTTPException(status_code=404, detail="User not found")

        # Vérification des permissions
        if requester.role == UserRole.STUDENT:
            if requester_id != user_id:
                raise HTTPException(
                    status_code=status.HTTP_403_FORBIDDEN,
                    detail="Students can only update their own profile"
                )
            # Limiter les champs modifiables par l'étudiant
            allowed_fields = {'slack_id', 'password'}
            kwargs = {k: v for k, v in kwargs.items() if k in allowed_fields}

        elif requester.role == UserRole.STAFF:
            if target.role == UserRole.ADMIN:
                raise HTTPException(
                    status_code=status.HTTP_403_FORBIDDEN,
                    detail="Staff cannot modify admin accounts"
                )

        # Mise à jour des champs
        for key, value in kwargs.items():
            if key == 'password':
                salt = bcrypt.gensalt()
                hashed_password = bcrypt.hashpw(value.encode('utf-8'), salt)
                setattr(target, 'password_hash', hashed_password.decode('utf-8'))
            elif hasattr(target, key):
                setattr(target, key, value)

        db.commit()
        db.refresh(target)
        return target

    @staticmethod
    def deactivate_user(
        db: Session,
        user_id: int,
        requester_id: int
    ) -> bool:
        """
        Désactive un utilisateur (soft delete)
        - Admin peut désactiver n'importe qui sauf autres admins
        - Staff peut désactiver des étudiants
        """
        requester = db.query(User).filter(User.id == requester_id).first()
        target = db.query(User).filter(User.id == user_id).first()

        if not requester or not target:
            raise HTTPException(status_code=404, detail="User not found")

        if requester.role == UserRole.STUDENT:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Students cannot deactivate accounts"
            )

        if requester.role == UserRole.STAFF:
            if target.role != UserRole.STUDENT:
                raise HTTPException(
                    status_code=status.HTTP_403_FORBIDDEN,
                    detail="Staff can only deactivate student accounts"
                )

        if requester.role == UserRole.ADMIN and target.role == UserRole.ADMIN:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Admins cannot deactivate other admin accounts"
            )

        target.is_active = False
        db.commit()
        return True
    
    # ... [Code précédent reste identique jusqu'à la fin de deactivate_user] ...

    @staticmethod
    def get_staff_members(db: Session, requester_id: int) -> List[User]:
        """Liste tous les membres du staff"""
        requester = db.query(User).filter(User.id == requester_id).first()
        
        if not requester or requester.role == UserRole.STUDENT:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Only staff/admin can view staff members"
            )
        
        return db.query(User).filter(
            User.role == UserRole.STAFF,
            User.is_active == True
        ).all()

    @staticmethod
    def get_active_students_count_by_cohort(db: Session, cohort_id: int) -> int:
        """Compte les étudiants actifs d'une cohorte"""
        return db.query(Student).join(User).filter(
            Student.current_cohort_id == cohort_id,
            User.is_active == True
        ).count()

    @staticmethod
    def transfer_student(
        db: Session,
        student_id: int,
        new_cohort_id: int,
        requester_id: int,
        reason: str
    ) -> Student:
        """Transfert d'un étudiant vers une nouvelle cohorte"""
        requester = db.query(User).filter(
            User.id == requester_id,
            User.role.in_([UserRole.ADMIN, UserRole.STAFF])
        ).first()

        if not requester:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Only staff/admin can transfer students"
            )

        student = db.query(Student).filter(Student.id == student_id).first()
        if not student:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Student not found"
            )

        # Création de l'historique
        history_entry = StudentCohortHistory(
            student_id=student.id,
            cohort_id=student.current_cohort_id,
            start_date=datetime.utcnow(),  # On pourrait améliorer avec la date réelle de début
            end_date=datetime.utcnow(),
            reason=reason
        )
        
        # Mise à jour de la cohorte
        student.current_cohort_id = new_cohort_id
        
        db.add(history_entry)
        db.commit()
        db.refresh(student)
        
        return student

    @staticmethod
    def bulk_create_students(
        db: Session,
        creator_id: int,
        students_data: List[dict],
        cohort_id: int
    ) -> List[User]:
        """Création en masse d'étudiants"""
        creator = db.query(User).filter(
            User.id == creator_id,
            User.role.in_([UserRole.ADMIN, UserRole.STAFF])
        ).first()

        if not creator:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Only staff/admin can create students"
            )

        created_users = []
        try:
            for student_data in students_data:
                # Hash du mot de passe
                salt = bcrypt.gensalt()
                hashed_password = bcrypt.hashpw(
                    student_data['password'].encode('utf-8'),
                    salt
                )

                # Création de l'utilisateur
                user = User(
                    email=student_data['email'],
                    password_hash=hashed_password.decode('utf-8'),
                    role=UserRole.STUDENT,
                    first_name=student_data['first_name'],
                    last_name=student_data['last_name'],
                    slack_id=student_data.get('slack_id')
                )
                db.add(user)
                db.flush()  # Pour obtenir l'ID sans commit

                # Création du profil étudiant
                student = Student(
                    user_id=user.id,
                    current_cohort_id=cohort_id
                )
                db.add(student)
                created_users.append(user)

            db.commit()
            return created_users

        except IntegrityError:
            db.rollback()
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Error during bulk creation. Check for duplicate emails."
            )

    @staticmethod
    def validate_student_availability(
        db: Session,
        student_id: int,
        date: datetime
    ) -> bool:
        """Vérifie la disponibilité d'un étudiant"""
        student = db.query(Student).filter(Student.id == student_id).first()
        if not student:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Student not found"
            )

        # Vérification des indisponibilités personnelles
        unavailable = db.query(StudentUnavailability).filter(
            StudentUnavailability.student_id == student_id,
            StudentUnavailability.start_date <= date,
            StudentUnavailability.end_date >= date,
            StudentUnavailability.status == 'validated'
        ).first()
        
        if unavailable:
            return False

        # Vérification des vacances de la cohorte
        cohort = db.query(Cohort).filter(
            Cohort.id == student.current_cohort_id
        ).first()
        
        if cohort and cohort.pause_periods:
            for period in cohort.pause_periods:
                if period['start_date'] <= date <= period['end_date']:
                    return False

        # Vérification des jours fériés
        holiday = db.query(PublicHoliday).filter(
            PublicHoliday.date == date.date()
        ).first()
        
        if holiday:
            return False

        return True