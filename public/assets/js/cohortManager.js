/**
 * Cohort management module
 * This module handles all cohort-related operations including listing, adding, and deleting cohorts
 */

const CohortManager = {
    deleteButton: document.getElementById('delete-cohort'),
    selectedCohortId: null,
    isSubmitting: false,

    init() {
        this.setupEventListeners();
    },

    setupEventListeners() {
        this.deleteButton.addEventListener('click', () => this.deleteCohort());
        document.getElementById('new-cohort-form').addEventListener('submit', (e) => this.createCohort(e));
    },

    loadCohorts() {
        fetch('/configuration/cohortes')
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            })
            .then(data => this.displayCohorts(data))
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('existing-cohorts').innerHTML = '<p>Erreur lors du chargement des cohortes.</p>';
            });
    },

    displayCohorts(cohorts) {
        const cohortList = document.getElementById('existing-cohorts');
        cohortList.innerHTML = '<h4>Cohortes existantes</h4>';
        if (cohorts.length === 0) {
            cohortList.innerHTML += '<p>Aucune cohorte existante.</p>';
        } else {
            cohorts.forEach(cohort => {
                const btn = document.createElement('button');
                btn.textContent = cohort.nom;
                btn.classList.add('neumorphic-button', 'cohort-button');
                btn.dataset.cohortId = cohort.id;
                btn.addEventListener('click', () => this.selectCohort(cohort.id, btn));
                cohortList.appendChild(btn);
            });
        }
    },

    selectCohort(cohortId, button) {
        this.selectedCohortId = cohortId;
        document.querySelectorAll('#existing-cohorts button').forEach(btn => {
            btn.classList.remove('active');
        });
        button.classList.add('active');
        this.deleteButton.style.display = 'block';
    },

    deleteCohort() {
        if (this.selectedCohortId && confirm('Êtes-vous sûr de vouloir supprimer cette cohorte ?')) {
            fetch(`/configuration/cohortes/${this.selectedCohortId}`, { method: 'DELETE' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.loadCohorts();
                        this.deleteButton.style.display = 'none';
                        this.selectedCohortId = null;
                    } else {
                        console.error('Erreur lors de la suppression de la cohorte');
                    }
                })
                .catch(error => console.error('Erreur:', error));
        }
    },

    createCohort(e) {
        e.preventDefault();
        if (this.isSubmitting) return;
        this.isSubmitting = true;

        const nom = document.getElementById('new-cohort-name').value.trim();
        if (!nom) {
            alert('Le nom de la cohorte ne peut pas être vide.');
            this.isSubmitting = false;
            return;
        }

        fetch('/configuration/cohortes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nom: nom }),
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Erreur réseau');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.loadCohorts();
                document.getElementById('new-cohort-name').value = '';
            } else {
                alert(data.message || 'Erreur lors de la création de la cohorte');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert(error.message || 'Erreur lors de la création de la cohorte. Veuillez réessayer.');
        })
        .finally(() => {
            this.isSubmitting = false;
        });
    }
};

export default CohortManager;