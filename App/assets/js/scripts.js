let categories = [];

// Fonction pour ajouter une nouvelle catégorie
function addCategory() {
    const categoryType = document.getElementById('category-type');
    const categoryName = document.getElementById('category-name');
    const categoryPrice = document.getElementById('category-price');
    const categoryContact = document.getElementById('category-contact');

    // Vérifier si les champs requis sont remplis
    if (!categoryType.value || !categoryName.value || !categoryPrice.value || isNaN(categoryPrice.value)) {
        alert('Veuillez remplir tous les champs correctement');
        return;
    }

    // Récupérer l'icône de la catégorie
    const selectedOption = categoryType.options[categoryType.selectedIndex];
    const icon = selectedOption.dataset.icon || 'fa-tag';

    // Récupérer le nom du contact
    const contactElement = categoryContact.options[categoryContact.selectedIndex];
    const contactName = contactElement ? contactElement.textContent : '';

    // Créer un objet représentant la catégorie
    const category = {
        id: Date.now(), // Identifiant unique
        type: categoryType.value,
        name: categoryName.value,
        price: parseFloat(categoryPrice.value),
        icon: icon,
        contactId: categoryContact.value,
        contactName: contactName
    };

    // Ajouter la catégorie au tableau
    categories.push(category);

    // Mettre à jour l'affichage des catégories
    renderCategories();

    // Mettre à jour le champ caché et le prix total
    updateCategoriesData();

    // Réinitialiser les champs du formulaire
    categoryName.value = '';
    categoryPrice.value = '';
    categoryType.selectedIndex = 0;
    categoryContact.selectedIndex = 0;
}

// Fonction pour supprimer une catégorie
function removeCategory(categoryId) {
    // Filtrer la catégorie à supprimer
    categories = categories.filter(category => category.id !== categoryId);

    // Mettre à jour l'affichage
    renderCategories();

    // Mettre à jour le champ caché et le prix total
    updateCategoriesData();
}

// Fonction pour mettre à jour le champ caché et le prix total
function updateCategoriesData() {
    document.getElementById('categories_data').value = JSON.stringify(categories);
    updateTotalPrice();
}

// Fonction pour afficher la liste des catégories
function renderCategories() {
    const categoriesList = document.getElementById('categories-list');
    categoriesList.innerHTML = '';

    if (categories.length === 0) {
        categoriesList.innerHTML = '<p class="no-categories">Aucune catégorie ajoutée</p>';
        return;
    }

    categories.forEach(category => {
        const categoryElement = document.createElement('div');
        categoryElement.className = 'category-item';
        categoryElement.innerHTML = `
            <div class="category-icon">
                <i class="fas ${escapeHtml(category.icon)}"></i>
            </div>
            <div class="category-details">
                <div class="category-header">
                    <p class="category-name">${escapeHtml(category.name)}</p>
                    <p class="category-price">${category.price.toFixed(2)} €</p>
                </div>
                <p class="category-contact">${category.contactName ? escapeHtml(category.contactName) : 'Non assigné'}</p>
                <input type="hidden" name="categories[${category.id}][type]" value="${escapeHtml(category.type)}">
                <input type="hidden" name="categories[${category.id}][name]" value="${escapeHtml(category.name)}">
                <input type="hidden" name="categories[${category.id}][price]" value="${category.price}">
                <input type="hidden" name="categories[${category.id}][contact_id]" value="${escapeHtml(category.contactId)}">
            </div>
            <div class="category-remove" onclick="removeCategory(${category.id})">
                <i class="fas fa-times"></i>
            </div>
        `;

        categoriesList.appendChild(categoryElement);
    });
}

// Fonction pour mettre à jour le prix total
function updateTotalPrice() {
    const totalPriceInput = document.getElementById('add-budget-price');
    const totalPrice = categories.reduce((sum, category) => sum + category.price, 0);
    totalPriceInput.value = totalPrice.toFixed(2);
}

// Fonction pour échapper les caractères HTML (sécurité)
function escapeHtml(text) {
    if (text === null || text === undefined) {
        return '';
    }
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Fonction pour charger les catégories existantes (pour l'édition de budget)
function loadExistingCategories() {
    const categoriesData = document.getElementById('existing_categories_data');
    if (categoriesData && categoriesData.value) {
        try {
            categories = JSON.parse(categoriesData.value);
            renderCategories();
            updateCategoriesData();
        } catch (e) {
            console.error('Erreur lors du chargement des catégories:', e);
        }
    }
}

// Initialiser les écouteurs d'événements une fois que le DOM est chargé
document.addEventListener('DOMContentLoaded', function () {
    const buttonHidden = document.getElementById('button-hidden');
    const items = document.getElementsByClassName('item');

    buttonHidden.addEventListener('click', () => {
        Array.from(items).forEach(element => {
            element.classList.toggle('hidden');
        });
    });


    document.getElementById('add-category-btn').addEventListener('click', addCategory);

    // Initialiser l'affichage des catégories
    renderCategories();

    // Charger les catégories existantes si on est en mode édition
    loadExistingCategories()
})