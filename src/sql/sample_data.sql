-- Sample data for diagnostic lab application
-- Insert test customers, admin users, packages, and appointments
-- Run after full_schema.sql

-- Clean existing data (optional - comment out if you want to keep existing data)
-- DELETE FROM appointment;
-- DELETE FROM diagnostic_packages;
-- DELETE FROM `user`;
-- DELETE FROM customers;
-- ALTER TABLE diagnostic_packages AUTO_INCREMENT = 1;
-- ALTER TABLE appointment AUTO_INCREMENT = 1;
-- ALTER TABLE `user` AUTO_INCREMENT = 1;
-- ALTER TABLE customers AUTO_INCREMENT = 1;

-- ==========================================
-- 1. ADMIN USERS (for admin panel login)
-- ==========================================
INSERT INTO `user` (`ID`, `Name`, `Gmail`, `username`, `Password`, `type`, `created_at`) VALUES
(1, 'Dr. Admin', 'admin@lab.com', 'admin', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', 'Admin', '2026-01-01 10:00:00'),
(2, 'Nurse Sarah', 'sarah@lab.com', 'sarah', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', 'User', '2026-01-05 12:30:00'),
(3, 'Dr. Kumar', 'kumar@lab.com', 'kumarkv', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', 'Admin', '2026-01-10 08:15:00'),
(4, 'Lab Technician John', 'john@lab.com', 'johntec', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', 'User', '2026-01-15 09:45:00'),
(5, 'Receptionist Lisa', 'lisa@lab.com', 'lisarec', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', 'User', '2026-01-20 11:00:00');

-- ==========================================
-- 2. FRONTEND CUSTOMERS
-- ==========================================
INSERT INTO `customers` (`name`, `email`, `phone`, `password`, `created_at`) VALUES
('Rajesh Patel', 'rajesh.patel@email.com', '9876543210', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', '2026-02-01 14:20:00'),
('Priya Sharma', 'priya.sharma@email.com', '9876543211', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', '2026-02-03 09:30:00'),
('Amit Verma', 'amit.verma@email.com', '9876543212', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', '2026-02-05 16:45:00'),
('Neha Gupta', 'neha.gupta@email.com', '9876543213', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', '2026-02-08 10:15:00'),
('Vikram Singh', 'vikram.singh@email.com', '9876543214', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', '2026-02-10 13:50:00'),
('Anjali Desai', 'anjali.desai@email.com', '9876543215', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', '2026-02-12 11:20:00'),
('Rohan Nair', 'rohan.nair@email.com', '9876543216', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', '2026-02-15 15:30:00'),
('Divya Menon', 'divya.menon@email.com', '9876543217', '$2y$10$YIjlrBxJJuhLK.7f6q.4sO1PsEJ8.eXJJLiZBz6qxYzZN8v.B.h7e', '2026-02-18 08:45:00');

-- ==========================================
-- 3. DIAGNOSTIC PACKAGES
-- ==========================================
INSERT INTO `diagnostic_packages` (`id`, `name`, `description`, `pricing`, `category`, `tags`, `related_packages`, `popularity`, `created_at`) VALUES
(1, 'Full Body Checkup', 'Comprehensive health screening including blood tests, urine analysis, and ECG', 2500, 'General Health', '["blood", "urine", "ecg", "screening"]', '[2, 3, 4]', 95, '2026-01-01 08:00:00'),
(2, 'Complete Blood Count (CBC)', 'Detailed blood cell profile analysis', 800, 'Blood Tests', '["blood", "cbc", "cells"]', '[1, 5, 6]', 85, '2026-01-01 08:00:00'),
(3, 'Lipid Profile', 'Cholesterol and triglyceride testing for heart health', 1200, 'Cardiac Health', '["cholesterol", "lipid", "cardiac"]', '[1, 4, 12]', 90, '2026-01-01 08:00:00'),
(4, 'Liver Function Tests', 'Comprehensive liver health assessment', 1500, 'Organ Function', '["liver", "function", "enzymes"]', '[1, 5, 11]', 75, '2026-01-01 08:00:00'),
(5, 'Kidney Function Tests', 'Creatinine and urea levels analysis', 1000, 'Organ Function', '["kidney", "creatinine", "urea"]', '[1, 4, 6]', 80, '2026-01-01 08:00:00'),
(6, 'Thyroid Profile', 'T3, T4, and TSH hormone testing', 1300, 'Endocrine', '["thyroid", "tsh", "hormones"]', '[1, 2, 7]', 88, '2026-01-01 08:00:00'),
(7, 'Diabetes Screening', 'Fasting and post-meal glucose testing', 900, 'Metabolic', '["glucose", "diabetes", "fasting"]', '[1, 6, 8]', 92, '2026-01-01 08:00:00'),
(8, 'Vitamin B12 & Folate', 'Essential vitamin level assessment', 1100, 'Nutritional', '["vitamins", "b12", "folate"]', '[1, 7, 9]', 70, '2026-01-01 08:00:00'),
(9, 'Bone Density Test (DEXA)', 'Osteoporosis and bone health screening', 2000, 'Orthopedic', '["bone", "dexa", "osteoporosis"]', '[1, 10, 11]', 65, '2026-01-01 08:00:00'),
(10, 'Allergy Testing Panel', 'Comprehensive allergy sensitivity assessment', 1800, 'Immunology', '["allergy", "immune", "sensitivity"]', '[1, 9, 12]', 72, '2026-01-01 08:00:00'),
(11, 'Arthritis Panel', 'Rheumatoid factor and inflammation markers', 1400, 'Rheumatology', '["arthritis", "inflammation", "rf"]', '[4, 5, 9]', 68, '2026-01-01 08:00:00'),
(12, 'Cardiac Risk Assessment', 'Complete cardiac health evaluation', 3500, 'Cardiac Health', '["cardiac", "heart", "troponin"]', '[1, 3, 6]', 87, '2026-01-01 08:00:00');

-- ==========================================
-- 4. APPOINTMENTS (Various statuses)
-- ==========================================
-- SCHEDULED appointments (have future dates, no reports)
INSERT INTO `appointment` (`name`, `email`, `phone`, `package`, `date`, `report`, `created_at`, `patient_name`, `test_name`) VALUES
('Rajesh Patel', 'rajesh.patel@email.com', '9876543210', 'Full Body Checkup', '2026-04-05 10:00:00', NULL, '2026-03-15 09:30:00', 'Rajesh Patel', 'Full Body Checkup'),
('Priya Sharma', 'priya.sharma@email.com', '9876543211', 'Complete Blood Count (CBC)', '2026-04-08 11:30:00', NULL, '2026-03-16 14:20:00', 'Priya Sharma', 'Complete Blood Count (CBC)'),
('Amit Verma', 'amit.verma@email.com', '9876543212', 'Thyroid Profile', '2026-04-10 14:00:00', NULL, '2026-03-18 10:15:00', 'Amit Verma', 'Thyroid Profile'),
('Neha Gupta', 'neha.gupta@email.com', '9876543213', 'Lipid Profile', '2026-04-12 09:00:00', NULL, '2026-03-20 11:45:00', 'Neha Gupta', 'Lipid Profile'),

-- COMPLETED appointments (have past dates with reports)
('Vikram Singh', 'vikram.singh@email.com', '9876543214', 'Diabetes Screening', '2026-03-15 10:30:00', '{"test_date": "2026-03-15", "glucose_fasting": 95, "glucose_pp": 120, "status": "normal", "notes": "All values within normal range"}', '2026-03-10 08:00:00', 'Vikram Singh', 'Diabetes Screening'),
('Anjali Desai', 'anjali.desai@email.com', '9876543215', 'Full Body Checkup', '2026-03-20 11:00:00', '{"test_date": "2026-03-20", "blood_test": "normal", "urine": "normal", "ecg": "normal", "doctor_notes": "Patient in good health condition"}', '2026-03-12 09:20:00', 'Anjali Desai', 'Full Body Checkup'),
('Rohan Nair', 'rohan.nair@email.com', '9876543216', 'Thyroid Profile', '2026-03-22 13:15:00', '{"test_date": "2026-03-22", "tsh": 2.5, "t3": 110, "t4": 8.5, "status": "normal"}', '2026-03-18 15:30:00', 'Rohan Nair', 'Thyroid Profile'),
('Divya Menon', 'divya.menon@email.com', '9876543217', 'Kidney Function Tests', '2026-03-25 10:45:00', '{"test_date": "2026-03-25", "creatinine": 0.8, "urea": 28, "status": "normal", "notes": "Kidney function optimal"}', '2026-03-19 12:00:00', 'Divya Menon', 'Kidney Function Tests'),

-- PENDING appointments (no date set yet, no report)
('Rajesh Patel', 'rajesh.patel@email.com', '9876543210', 'Liver Function Tests', NULL, NULL, '2026-03-25 16:20:00', 'Rajesh Patel', 'Liver Function Tests'),
('Priya Sharma', 'priya.sharma@email.com', '9876543211', 'Cardiac Risk Assessment', NULL, NULL, '2026-03-26 10:30:00', 'Priya Sharma', 'Cardiac Risk Assessment'),
('Amit Verma', 'amit.verma@email.com', '9876543212', 'Allergy Testing Panel', NULL, NULL, '2026-03-27 14:45:00', 'Amit Verma', 'Allergy Testing Panel');
