class StudentList {
    constructor() {
        this.studentsList = document.getElementById('students-list');
        this.studentTable = document.getElementById('students-table');
    }

    displayStudents(students, cohortId) {
        this.studentsList.style.display = 'block';
        const tbody = this.studentTable.querySelector('tbody');
        tbody.innerHTML = '';
        students.forEach(student => {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td><input type="checkbox" class="student-select" data-id="${student.id}"></td>
                <td>${student.nom}</td>
                <td>${student.prenom}</td>
                <td>${student.email}</td>
                <td>${student.indisponibilite || ''}</td>
                <td>
                    <button class="edit-student neumorphic-button neumorphic-button-small" data-id="${student.id}">Éditer</button>
                    <button class="delete-student neumorphic-button neumorphic-button-small danger" data-id="${student.id}">Supprimer</button>
                </td>
            `;
        });
        this.studentTable.style.display = 'table';
        this.setupStudentEventListeners();
    }

    setupStudentEventListeners() {
        document.querySelectorAll('.edit-student').forEach(button => {
            button.addEventListener('click', (e) => StudentManager.studentForm.editStudent(e.target.dataset.id));
        });
        document.querySelectorAll('.delete-student').forEach(button => {
            button.addEventListener('click', (e) => this.deleteStudent(e.target.dataset.id));
        });
    }

    deleteStudent(studentId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')) {
            StudentManager.studentAPI.deleteStudent(studentId)
                .then(data => {
                    if (data.success) {
                        document.querySelector(`[data-id="${studentId}"]`).closest('tr').remove();
                    } else {
                        alert('Erreur lors de la suppression de l\'étudiant');
                    }
                })
                .catch(error => console.error('Erreur:', error));
        }
    }
}

export default StudentList;