-- Clinic Appointment System
-- Run this file in phpMyAdmin (Import tab, or paste into the SQL tab)

CREATE DATABASE IF NOT EXISTS clinic_appointment_system;
USE clinic_appointment_system;

-- Drop in child-to-parent order so the script can be re-run safely
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS doctors;

-- ---------------------------------------------
-- Table: doctors
-- ---------------------------------------------
CREATE TABLE doctors (
    doctor_id      INT AUTO_INCREMENT PRIMARY KEY,
    doctor_name    VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    phone          VARCHAR(20)  NOT NULL,
    email          VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Table: patients
-- ---------------------------------------------
CREATE TABLE patients (
    patient_id   INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100) NOT NULL,
    age          INT NOT NULL,
    gender       ENUM('Male','Female','Other') NOT NULL,
    phone        VARCHAR(20)  NOT NULL,
    email        VARCHAR(100) NULL,
    address      VARCHAR(255) NULL
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Table: appointments
-- ---------------------------------------------
CREATE TABLE appointments (
    appointment_id   INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id        INT NOT NULL,
    patient_id       INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason           VARCHAR(255) NOT NULL,
    status           ENUM('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
    CONSTRAINT fk_appointments_doctor
        FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_appointments_patient
        FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Sample data
-- ---------------------------------------------
INSERT INTO doctors (doctor_name, specialization, phone, email) VALUES
('Dr. Anil Mehta',  'Cardiology',       '9876543210', 'anil.mehta@clinic.com'),
('Dr. Priya Shah',  'Pediatrics',       '9876543211', 'priya.shah@clinic.com'),
('Dr. Rahul Patel', 'General Medicine', '9876543212', 'rahul.patel@clinic.com'),
('Dr. Neha Joshi',  'Dermatology',      '9876543213', 'neha.joshi@clinic.com');

INSERT INTO patients (patient_name, age, gender, phone, email, address) VALUES
('Amit Desai',  35, 'Male',   '9123456780', 'amit.desai@example.com',  '12 Shivam Society, Ahmedabad'),
('Kavita Rao',  28, 'Female', '9123456781', 'kavita.rao@example.com',  '45 Green Park, Ahmedabad'),
('Rohan Verma',  8, 'Male',   '9123456782', NULL,                      '7 Lake View, Ahmedabad'),
('Sneha Iyer',  52, 'Female', '9123456783', 'sneha.iyer@example.com',  '90 Sunrise Apartments, Ahmedabad'),
('Karan Thakkar', 41, 'Male', '9123456784', 'karan.t@example.com',     NULL);

-- Dates use CURDATE() so the dashboard "Today's Appointments" shows data when you demo
INSERT INTO appointments (doctor_id, patient_id, appointment_date, appointment_time, reason, status) VALUES
(1, 1, CURDATE(),                          '10:00:00', 'Chest pain checkup', 'Scheduled'),
(2, 3, CURDATE(),                          '11:30:00', 'Fever and cough',    'Scheduled'),
(3, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:30:00', 'General checkup',    'Scheduled'),
(4, 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '14:00:00', 'Skin allergy',       'Completed'),
(1, 5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '16:00:00', 'Blood pressure review', 'Cancelled');