// Mise à jour de la catégorie de recherche
function updateFormAction() {
    let form = document.getElementById('search-form');
    let value = document.getElementById('search-category').value;
    if (value === 'contacts') {
        form.action = 'index.php?route=search-contact';
    } else {
        form.action = 'index.php?route=search-user';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    let form = document.getElementById('search-form');

    if (form) {
        let searchCategory = document.getElementById('search-category');
        // Changement de la valeur de la catégorie de recherche
        searchCategory.addEventListener('change', updateFormAction);
    }
});