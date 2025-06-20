let isEditMode = false;
let categories = [];
let budget = { categories: [] };
let currentParticipants = [];
let editingCategoryIndex = -1;

/**
 * Calcule le montant total de la catégorie depuis le champ input.
 * @returns {number} Le montant de la catégorie.
 */
function calculateCategoryAmount() {
    const amountInput = document.getElementById('category-amount');
    return parseFloat(amountInput.value) || 0.00;
}


/**
 * Attache les écouteurs d'événements pour les boutons de suppression de participant.
 */
function setupParticipantEvents() {
    document.querySelectorAll('.remove-participant').forEach(button => {
         button.addEventListener('click', handleRemoveParticipant);
    });
}

 /**
 * Gère la suppression d'un participant de la liste temporaire.
 */
function handleRemoveParticipant() {
    const index = parseInt(this.getAttribute('data-index'));
    if (!isNaN(index) && index >= 0 && index < currentParticipants.length) {
        currentParticipants.splice(index, 1);
        loadParticipantsForCategory(currentParticipants);
    }
}


/**
 * Affiche les participants pour la catégorie en cours d'édition dans la table du formulaire.
 * @param {Array} participants - La liste des participants à afficher.
 */
function loadParticipantsForCategory(participants) {
    const participantsTable = document.querySelector('.category-info table tbody');
    if (!participantsTable) return;

    participantsTable.innerHTML = '';
    if (!participants || participants.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="3">Aucun participant</td>';
        participantsTable.appendChild(row);
        return;
    }

    const totalAmount = calculateCategoryAmount();
    const participantAmount = participants.length > 0 ? (totalAmount / participants.length): 0;

    participants.forEach((participant, index) => {
        participant.amount = parseFloat(participantAmount);
        if (isNaN(participant.amount)) {
            participant.amount = 0;
        }

        const row = document.createElement('tr');
        const participantName = typeof participant.name === 'string' ? participant.name : 'Nom invalide';
        row.innerHTML = `
            <td>${participantName}</td>
            <td>${participant.amount.toFixed(2)} €</td>
            <td>
                <button type="button" class="remove-participant button-icon" data-index="${index}">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        participantsTable.appendChild(row);
    });
    setupParticipantEvents();
}


/**
 * Met à jour la valeur du champ caché contenant les données des catégories en JSON.
 */
function updateCategoriesData() {
    const categoriesDataInput = document.getElementById('categories_data');
    if (categoriesDataInput) {
        categoriesDataInput.value = JSON.stringify(categories);
    }
}


/**
 * Met à jour le champ affichant le prix total du budget.
 */
function updateBudgetPrice() {
    const totalPrice = categories.reduce((sum, category) => {
        const price = parseFloat(category.price);
        return sum + price;
    }, 0);
    const inputId = isEditMode ? 'edit-budget-price' : 'add-budget-price';
    const budgetPriceInput = document.getElementById(inputId);
    if (budgetPriceInput) {
        budgetPriceInput.value = totalPrice.toFixed(2);
    }
}


/**
 * Attache les écouteurs d'événements pour les boutons Modifier/Supprimer des catégories.
 */
function setupCategoryEvents() {
    const categoriesList = document.getElementById('categories-list');
    if (!categoriesList) return;

    categoriesList.removeEventListener('click', handleCategoryClick);
    
    categoriesList.addEventListener('click', handleCategoryClick);
}

/**
 * Gestionnaire unique pour tous les clics sur les catégories
 */
function handleCategoryClick(event) {
    const target = event.target.closest('button');
    if (!target) return;

    if (target.classList.contains('edit-category')) {
        const index = parseInt(target.getAttribute('data-index'));
        if (!isNaN(index)) {
            editCategory(index);
        }
    } else if (target.classList.contains('remove-category')) {
        const index = parseInt(target.getAttribute('data-index'));
        if (!isNaN(index) && index >= 0 && index < categories.length) {
            categories.splice(index, 1);
            renderCategories();
        }
    }
}

/**
 * Gère le clic sur le bouton de modification d'une catégorie.
 */
function handleEditCategory() {
     const index = parseInt(this.getAttribute('data-index'));
     if (!isNaN(index)) {
        editCategory(index);
     }
}


/**
 * Gère le clic sur le bouton Supprimer d'une catégorie.
 */
function handleRemoveCategory() {
    const index = parseInt(this.getAttribute('data-index'));
     if (!isNaN(index) && index >= 0 && index < categories.length) {
        categories.splice(index, 1);
        renderCategories(); // Mettre à jour l'affichage de la liste
    }
}

/**
 * Affiche la liste des catégories ajoutées/existantes.
 */
function renderCategories() {
    const categoriesList = document.getElementById('categories-list');
    if (!categoriesList) return;
    
    categoriesList.innerHTML = '';
    categories.forEach((category, index) => {
        const categoryElement = document.createElement('div');
        categoryElement.className = 'category-item';

        // Vérification et formatage des participants
        let participantsHtml = '<p>Aucun participant</p>';
         if (category.participants && category.participants.length > 0) {
            participantsHtml = '<ul>' +
                category.participants.map(p => {
                     const name = typeof p.name === 'string' ? p.name : 'Nom invalide';
                     const amount = parseFloat(p.amount);
                     const amountDisplay = !isNaN(amount) ? amount.toFixed(2) : 'N/A';
                     return `<li>${name} - ${amountDisplay} €</li>`;
                }).join('') + '</ul>';
        }

        // Vérification et formatage du prix
        const price = parseFloat(category.price);
        const priceDisplay = !isNaN(price) ? price.toFixed(2) : '0.00';
        const iconClass = typeof category.icon === 'string' ? category.icon : 'fa-question-circle';
        const categoryName = typeof category.name === 'string' ? category.name : 'Nom invalide';

        categoryElement.innerHTML = `
            <div class="category-header">
                <i class="fas ${iconClass}"></i>
                <h3>${categoryName}</h3>
                <p class="category-price">${priceDisplay} €</p>
            </div>
            <div class="category-participants">
                <p>Participants (${category.participants ? category.participants.length : 0}):</p>
                ${participantsHtml}
            </div>
            <div class="category-actions">
                <button type="button" class="edit-category button-icon" data-index="${index}">
                    <i class="fas fa-edit"></i> Modifier
                </button>
                <button type="button" class="remove-category button-icon" data-index="${index}">
                    <i class="fas fa-times"></i> Supprimer
                </button>
            </div>
        `;
        categoriesList.appendChild(categoryElement);
    });
    // Attacher les événements aux nouveaux boutons créés
    setupCategoryEvents();
    // Mettre à jour les données cachées et le prix total
    updateCategoriesData();
    updateBudgetPrice();
}


/**
 * Pré-remplit le formulaire d'édition avec les données d'une catégorie existante.
 * @param {number} index - L'index de la catégorie à éditer dans le tableau `categories`.
 */
function editCategory(index) {
    if (index < 0 || index >= categories.length) {
         resetCategoryForm();
         return;
    }

    const category = categories[index];

    // Vérifications des valeurs
    const categoryType = document.getElementById('category-type');
    const categoryName = document.getElementById('category-name');
    const categoryPrice = document.getElementById('category-amount');

    categoryType.value = category.type || '';
    categoryName.value = category.name || '';
    categoryPrice.value = category.price || 0;

    // Copier des participants
    currentParticipants = category.participants ? category.participants.map(p => ({ ...p })) : [];
    editingCategoryIndex = index;

    loadParticipantsForCategory(currentParticipants); // Afficher les participants dans le formulaire

    const addCategoryBtn = document.getElementById('add-category-btn');
    if(addCategoryBtn) {
        addCategoryBtn.textContent = 'Mettre à jour la catégorie';
    }
}


/**
 * Réinitialise les champs du formulaire d'ajout/édition de catégorie.
 */
function resetCategoryForm() {
    // Vérifications des valeurs
    const categoryType = document.getElementById('category-type');
    const categoryName = document.getElementById('category-name');
    const categoryPrice = document.getElementById('category-amount');
    const categoryContact = document.getElementById('category-contact');

    categoryType.value = '';
    categoryName.value = '';
    categoryPrice.value = '0';
    categoryContact.value = '';
    currentParticipants = [];
    editingCategoryIndex = -1;

    loadParticipantsForCategory(currentParticipants); // Vider la table des participants du formulaire
    const addCategoryBtn = document.getElementById('add-category-btn');
    if(addCategoryBtn) {
        addCategoryBtn.textContent = 'Ajouter cette catégorie';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    isEditMode = window.location.href.includes('edit-budget');
    // Chargement des données initiales (si mode édition)
    const budgetData = document.getElementById('budget_data');
    if (isEditMode && budgetData) {
        try {
            const budgetJson = budgetData.value;
            budget = JSON.parse(budgetJson);
            if (budget && Array.isArray(budget.categories)) {
                categories = budget.categories.map(category => ({
                    id: category.id || 0,
                    type: category.type || '',
                    name: category.name || '',
                    icon: 'fa-' + (category.type || 'question-circle'),
                    price: parseFloat(category.price) || 0,
                    participants: Array.isArray(category.participants) ? category.participants.map(p => ({
                        id: p.id || 0,
                        name: `${p.username || ''} #${p.unique_id || 0}`,
                        amount: parseFloat(p.amount) || 0,
                        username: p.username,
                        unique_id: p.unique_id
                    })) : []
                }));
            } else {
                 budget.categories = [];
                 categories = [];
            }
        } catch (e) {
            console.error("Erreur lors du parsing des données JSON du budget:", e);
            budget = { categories: [] };
            categories = [];
        }
    } else {
         budget = { categories: [] };
         categories = [];
    }

    const updateBudgetForm = document.querySelector('form[action="index.php?route=update-budget"]');
    if (updateBudgetForm) {
        updateBudgetForm.addEventListener('submit', function(e) {
            console.log('Formulaire soumis');
            
            updateCategoriesData();
            
            if (categories.length === 0) {
                e.preventDefault();
                alert('Veuillez ajouter au moins une catégorie au budget.');
                return false;
            }
            
            console.log('Categories à envoyer:', categories);
            console.log('Categories JSON:', JSON.stringify(categories));
            
            return true;
        });
    }

    // --- Attachement des Écouteurs d'Événements ---
    const categoryContactSelect = document.getElementById('category-contact');
    if (categoryContactSelect) {
        categoryContactSelect.addEventListener('change', function() {
            const contactId = this.value;
            if (!contactId) return;

            const selectedOption = this.options[this.selectedIndex];
            const contactText = selectedOption ? selectedOption.textContent.trim() : '';
            const contactUsername = selectedOption ? selectedOption.getAttribute('data-username') : '';
            const contactUniqueId = selectedOption ? selectedOption.getAttribute('data-uniqueid') : '';

            // Vérifier si le participant est déjà ajouté
            if (currentParticipants.some(p => p.id == contactId)) {
                alert('Ce contact est déjà ajouté à la catégorie');
                this.value = '';
                return;
            }
            // Ajouter le nouveau participant
            const newParticipant = {
                id: contactId,
                name: contactText,
                amount: 0.00,
                username: contactUsername,
                unique_id: contactUniqueId
            };
            currentParticipants.push(newParticipant);
            loadParticipantsForCategory(currentParticipants);
            this.value = '';
        });
    }

    const addCategoryButton = document.getElementById('add-category-btn');
    if (addCategoryButton) {
        addCategoryButton.addEventListener('click', () => {
            const categoryType = document.getElementById('category-type').value;
            const categoryName = document.getElementById('category-name').value;
            const categoryIcon = categoryType ? 'fa-' + categoryType : 'fa-question-circle';
            const categoryPrice = calculateCategoryAmount();
            if (!categoryType || !categoryName) {
                alert('Veuillez sélectionner un type et entrer un nom pour la catégorie.');
                return;
            }
            if (currentParticipants.length === 0) {
                alert('Veuillez ajouter au moins un participant à cette catégorie.');
                return;
            }
             // S'assurer que les montants des participants sont corrects avant de sauvegarder
             const participantAmount = currentParticipants.length > 0 ? (categoryPrice / currentParticipants.length) : 0;
             const finalParticipants = currentParticipants.map(p => ({
                 ...p,
                 amount: parseFloat(participantAmount)
             }));
            if (editingCategoryIndex >= 0) {
                // --- Mode Edition ---
                if (editingCategoryIndex < categories.length) {
                    categories[editingCategoryIndex] = {
                        ...categories[editingCategoryIndex],
                        type: categoryType,
                        name: categoryName,
                        icon: categoryIcon,
                        price: categoryPrice,
                        participants: finalParticipants
                    };
                } else {
                     resetCategoryForm();
                     renderCategories();
                     return;
                }
            } else {
                // --- Mode Ajout ---
                categories.push({
                    id: 0,
                    type: categoryType,
                    name: categoryName,
                    icon: categoryIcon,
                    price: categoryPrice,
                    participants: finalParticipants
                });
            }
            resetCategoryForm();
            renderCategories();
        });
    }

    const categoryAmountInput = document.getElementById('category-amount');
    if (categoryAmountInput) {
        categoryAmountInput.addEventListener('input', function() {
            // Recalculer et afficher les montants par participant à chaque changement du total
            loadParticipantsForCategory(currentParticipants);
        });
    }
    renderCategories(); // Afficher la liste initiale des catégories
    if (isEditMode && categories.length > 0) {
        // En mode édition, pré-remplir le formulaire avec la première catégorie
         editCategory(0);
    } else {
         // En mode ajout, s'assurer que le formulaire est vide au départ
         resetCategoryForm();
    }
});