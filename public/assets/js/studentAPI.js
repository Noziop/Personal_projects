class StudentAPI {
    fetchCohorts() {
        return fetch('/configuration/cohortes')
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            });
    }

    fetchStudents(cohortId) {
        return fetch(`/configuration/students/cohort/${cohortId}`)
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            });
    }

    fetchStudent(studentId) {
        return fetch(`/configuration/students/${studentId}`)
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            });
    }

    saveStudent(studentId, studentData) {
        const method = studentId ? 'PUT' : 'POST';
        const url = studentId ? `/configuration/students/${studentId}` : '/configuration/students';
        return fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(studentData),
        })
        .then(response => response.json());
    }

    deleteStudent(studentId) {
        return fetch(`/configuration/students/${studentId}`, { method: 'DELETE' })
            .then(response => response.json());
    }

    bulkDeleteStudents(studentIds) {
        return fetch('/configuration/students/bulk-delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: studentIds }),
        })
        .then(response => response.json());
    }

    loadCohortOptions() {
        return this.fetchCohorts()
            .then(data => {
                const cohortSelect = document.getElementById('student-cohort');
                cohortSelect.innerHTML = '';
                data.forEach(cohort => {
                    const option = document.createElement('option');
                    option.value = cohort.id;
                    option.textContent = cohort.nom;
                    cohortSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Erreur lors du chargement des cohortes:', error));
    }
}

export default StudentAPI;