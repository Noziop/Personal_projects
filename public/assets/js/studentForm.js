class StudentForm {
    constructor() {
        this.studentForm = document.getElementById('student-form');
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.studentForm.addEventListener('submit', (e) => this.saveStudent(e));
        document.getElementById('cancel-student-edit').addEventListener('click', () => this.hideStudentForm());
    }

    showAddStudentForm() {
        document.getElementById('student-id').value = '';
        document.getElementById('student-nom').value = '';
        document.getElementById('student-prenom').value = '';
        document.getElementById('student-email').value = '';
        document.getElementById('student-indisponibilite').value = '';
        document.getElementById('student-cohort').value = document.getElementById('add-student').dataset.cohortId;
        StudentManager.studentAPI.loadCohortOptions();
        this.showStudentForm('Ajouter un nouvel étudiant');
    }

    editStudent(studentId) {
        StudentManager.studentAPI.fetchStudent(studentId)
            .then(student => {
                document.getElementById('student-id').value = student.id;
                document.getElementById('student-nom').value = student.nom;
                document.getElementById('student-prenom').value = student.prenom;
                document.getElementById('student-email').value = student.email;
                document.getElementById('student-cohort').value = student.cohorte_id;
                document.getElementById('student-indisponibilite').value = student.indisponibilite || '';
                StudentManager.studentAPI.loadCohortOptions();
                this.showStudentForm('Éditer l\'étudiant');
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la récupération des données de l\'étudiant');
            });
    }

    saveStudent(e) {
        e.preventDefault();
        const studentId = document.getElementById('student-id').value;
        const studentData = {
            nom: document.getElementById('student-nom').value,
            prenom: document.getElementById('student-prenom').value,
            email: document.getElementById('student-email').value,
            cohorte_id: document.getElementById('student-cohort').value,
            indisponibilite: document.getElementById('student-indisponibilite').value
        };
        
        StudentManager.studentAPI.saveStudent(studentId, studentData)
            .then(data => {
                if (data.success) {
                    StudentManager.loadStudents(studentData.cohorte_id);
                    this.hideStudentForm();
                } else {
                    alert('Erreur lors de la sauvegarde de l\'étudiant');
                }
            })
            .catch(error => console.error('Erreur:', error));
    }

    showStudentForm(title) {
        document.getElementById('student-form-title').textContent = title;
        this.studentForm.style.display = 'block';
    }

    hideStudentForm() {
        this.studentForm.style.display = 'none';
    }

    showBulkEditForm(selectedStudents) {
        // Implement bulk edit form logic here
        alert('Bulk edit functionality to be implemented');
    }
}

export default StudentForm;