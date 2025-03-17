-- Ajouter des utilisateurs
INSERT INTO users (name, email, password_hash) VALUES 
('Alice', 'alice@example.com', 'hashed_password_1'),
('Bob', 'bob@example.com', 'hashed_password_2'),
('Charlie', 'charlie@example.com', 'hashed_password_3');

-- Ajouter un groupe
INSERT INTO `groups` (name, created_by) VALUES ('Vacances Espagne', 1);

-- Ajouter des membres au groupe
INSERT INTO group_members (user_id, group_id) VALUES (1,0), (2,0), (3,0);

-- Ajouter des dépenses
INSERT INTO expenses (group_id, payer_id, description, amount, currency, receipt_url) VALUES 
(1, 1, 'Dîner au restaurant', 75.50, 'EUR', 'https://example.com/receipt1.jpg'),
(1, 2, 'Location de voiture', 120.00, 'EUR', NULL),
(1, 3, 'Courses alimentaires', 45.75, 'EUR', 'https://example.com/receipt2.jpg');

-- Associer les participants aux dépenses
INSERT INTO expense_participants (expense_id, user_id, amount_owed) VALUES
(1, 2, 25.17), (1, 3, 25.17),
(2, 1, 40.00), (2, 3, 40.00),
(3, 1, 15.25), (3, 2, 15.25);

-- Ajouter des remboursements
INSERT INTO settlements (group_id, payer_id, receiver_id, amount, currency, status) VALUES 
(1, 2, 1, 25.17, 'EUR', 'completed'),
(1, 3, 2, 40.00, 'EUR', 'pending');
