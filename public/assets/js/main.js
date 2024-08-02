/**
 * Main application script
 * This script initializes all modules and sets up the main page interactions
 */

import CohortManager from './cohortManager.js';
import StudentManager from './studentManager.js';

document.addEventListener('DOMContentLoaded', function() {
    const btnCohortes = document.getElementById('btn-cohortes');
    const btnEtudiants = document.getElementById('btn-etudiants');
    const btnContraintes = document.getElementById('btn-contraintes');
    const sectionCohortes = document.getElementById('cohortes-form');
    const sectionEtudiants = document.getElementById('etudiants-form');
    const sectionContraintes = document.getElementById('contraintes-form');

    function hideAllSections() {
        sectionCohortes.style.display = 'none';
        sectionEtudiants.style.display = 'none';
        sectionContraintes.style.display = 'none';
    }

    btnCohortes.addEventListener('click', function() {
        hideAllSections();
        sectionCohortes.style.display = 'block';
        CohortManager.loadCohorts();
    });

    btnEtudiants.addEventListener('click', function() {
        hideAllSections();
        sectionEtudiants.style.display = 'block';
        StudentManager.loadCohortsForStudents();
    });

    btnContraintes.addEventListener('click', function() {
        hideAllSections();
        sectionContraintes.style.display = 'block';
    });

    // Initialize modules
    CohortManager.init();
    StudentManager.init();

    // Initially hide all sections
    hideAllSections();
});