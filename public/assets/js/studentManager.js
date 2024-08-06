import StudentList from './StudentList.js';
import StudentForm from './studentForm.js';
import StudentAPI from './studentAPI.js';

const StudentManager = {
    init() {
        this.studentList = new StudentList();
        this.studentForm = new StudentForm();
        this.studentAPI = new StudentAPI();
        this.setupEventListeners();
    },

    setupEventListeners() {
        document.getElementById('add-student').addEventListener('click', () => this.studentForm.showAddStudentForm());
        document.getElementById('edit-selected-students').addEventListener('click', () => this.editSelectedStudents());
        document.getElementById('delete-selected-students').addEventListener('click', () => this.deleteSelectedStudents());
    },

    loadCohortsForStudents() {
        this.studentAPI.fetchCohorts()
            .then(cohorts => this.displayCohortsForStudents(cohorts))
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('cohort-buttons').innerHTML = '<p>Erreur lors du chargement des cohortes.</p>';
            });
    },

    displayCohortsForStudents(cohorts) {
        const cohortButtonsContainer = document.getElementById('cohort-buttons');
        cohortButtonsContainer.innerHTML = '<h4>Sélectionnez une cohorte</h4>';
        if (cohorts.length === 0) {
            cohortButtonsContainer.innerHTML += '<p>Aucune cohorte existante.</p>';
        } else {
            cohorts.forEach(cohort => {
                const btn = document.createElement('button');
                btn.textContent = cohort.nom;
                btn.classList.add('neumorphic-button', 'cohort-button');
                btn.dataset.cohortId = cohort.id;
                btn.addEventListener('click', () => this.loadStudents(cohort.id));
                cohortButtonsContainer.appendChild(btn);
            });
        }
    },

    loadStudents(cohortId) {
        this.studentAPI.fetchStudents(cohortId)
            .then(students => this.studentList.displayStudents(students, cohortId))
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('students-list').innerHTML = '<p>Erreur lors du chargement des étudiants.</p>';
            });
    },

    editSelectedStudents() {
        const selectedStudents = document.querySelectorAll('.student-select:checked');
        if (selectedStudents.length === 0) {
            alert('Veuillez sélectionner au moins un étudiant à éditer.');
            return;
        }
        if (selectedStudents.length === 1) {
            this.studentForm.editStudent(selectedStudents[0].dataset.id);
        } else {
            this.studentForm.showBulkEditForm(selectedStudents);
        }
    },

    deleteSelectedStudents() {
        const selectedStudents = document.querySelectorAll('.student-select:checked');
        if (selectedStudents.length === 0) {
            alert('Veuillez sélectionner au moins un étudiant à supprimer.');
            return;
        }
        if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedStudents.length} étudiant(s) ?`)) {
            const studentIds = Array.from(selectedStudents).map(checkbox => checkbox.dataset.id);
            this.studentAPI.bulkDeleteStudents(studentIds)
                .then(data => {
                    if (data.success) {
                        selectedStudents.forEach(checkbox => checkbox.closest('tr').remove());
                    } else {
                        alert('Erreur lors de la suppression des étudiants');
                    }
                })
                .catch(error => console.error('Erreur:', error));
        }
    }
};

export default StudentManager;