CREATE DATABASE afyacheck;
USE afyacheck;

CREATE TABLE patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(100),
  age INT,
  gender VARCHAR(10),
  email VARCHAR(100),
  phone VARCHAR(20)
);

CREATE TABLE doctors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(100),
  specialty VARCHAR(100),
  email VARCHAR(100),
  phone VARCHAR(20)
);

CREATE TABLE assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  doctor_id INT,
  patient_id INT,
  assigned_at DATETIME,
  FOREIGN KEY (doctor_id) REFERENCES doctors(id),
  FOREIGN KEY (patient_id) REFERENCES patients(id)
);

CREATE TABLE bp_readings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT,
  systolic INT,
  diastolic INT,
  reading_time DATETIME,
  doctor_comment TEXT,
  FOREIGN KEY (patient_id) REFERENCES patients(id)
);