-- ============================================================
-- Campus Pulse — UIU  |  Database schema + seed data
-- Import this file in phpMyAdmin, or run:
--   mysql -u root -p < schema.sql
-- ============================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS campus_pulse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_pulse;

-- ---------- USERS ----------
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','faculty','admin') NOT NULL DEFAULT 'student',
  department VARCHAR(50) DEFAULT 'CSE',
  bio TEXT,
  avatar_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- NEWS (home feed) ----------
CREATE TABLE IF NOT EXISTS news (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(30) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  meta VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- EVENTS ----------
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(30) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  meta VARCHAR(255),
  status ENUM('approved','pending','rejected') NOT NULL DEFAULT 'pending',
  created_by INT DEFAULT NULL,
  interest_count INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- who already "pinged interested" on which event (prevents double count)
CREATE TABLE IF NOT EXISTS event_interests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  user_id INT NOT NULL,
  UNIQUE KEY uniq_interest (event_id, user_id),
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- RESOURCES (notes / question bank) ----------
CREATE TABLE IF NOT EXISTS resources (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kind ENUM('notes','qbank') NOT NULL,
  course_code VARCHAR(30) NOT NULL,
  title VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) DEFAULT NULL,
  uploader_id INT DEFAULT NULL,
  status ENUM('approved','pending','rejected') NOT NULL DEFAULT 'pending',
  downloads INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploader_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- ACHIEVEMENTS ----------
CREATE TABLE IF NOT EXISTS achievements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(30) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  meta VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- RESEARCH GRANTS ----------
CREATE TABLE IF NOT EXISTS research_grants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  status ENUM('approved','pending','rejected') NOT NULL DEFAULT 'pending',
  submitted_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- ALERTS (ticker) ----------
CREATE TABLE IF NOT EXISTS alerts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(30) NOT NULL DEFAULT 'Campus notice',
  message VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- CAMPUS STATUS (single row) ----------
CREATE TABLE IF NOT EXISTS campus_status (
  id INT PRIMARY KEY,
  status ENUM('normal','alert','critical') NOT NULL DEFAULT 'normal',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- demo accounts — password for ALL demo accounts is:  password123
INSERT INTO users (name, email, password_hash, role, department, bio) VALUES
('Shad', 'shad@uiu.ac.bd', '$2y$10$Kw9IIdTLgl3KjEXd9eJhxuxw1Q.9Vc9Z3MzpoO78j/1KrcfRVbYda', 'student', 'CSE', 'CSE student, building civic-tech side projects.'),
('Dr. Farhana Rahman', 'farhana@uiu.ac.bd', '$2y$10$Kw9IIdTLgl3KjEXd9eJhxuxw1Q.9Vc9Z3MzpoO78j/1KrcfRVbYda', 'faculty', 'CSE', 'Faculty member, CSE department.'),
('Admin Office', 'admin@uiu.ac.bd', '$2y$10$Kw9IIdTLgl3KjEXd9eJhxuxw1Q.9Vc9Z3MzpoO78j/1KrcfRVbYda', 'admin', 'Registrar', 'Registrar / admin office account.'),
('Tanvir Ahmed', 'tanvir@uiu.ac.bd', '$2y$10$Kw9IIdTLgl3KjEXd9eJhxuxw1Q.9Vc9Z3MzpoO78j/1KrcfRVbYda', 'student', 'EEE', 'EEE student.'),
('Nusrat Jahan', 'nusrat@uiu.ac.bd', '$2y$10$Kw9IIdTLgl3KjEXd9eJhxuxw1Q.9Vc9Z3MzpoO78j/1KrcfRVbYda', 'student', 'BBA', 'BBA student.'),
('Dr. Imran Kabir', 'imran@uiu.ac.bd', '$2y$10$Kw9IIdTLgl3KjEXd9eJhxuxw1Q.9Vc9Z3MzpoO78j/1KrcfRVbYda', 'faculty', 'EEE', 'Faculty member, EEE department.');

INSERT INTO news (category, title, description, meta) VALUES
('Academic', 'Mid-term routine for Summer 2026 published', 'Check department notice board for room allocation and seating plan.', 'Registrar office · 2h ago'),
('Admin', 'Class suspended — CSE building, floor 3', 'Water supply maintenance work in progress, expected to resume by evening.', 'Admin office · 10m ago'),
('Club', 'Photography Club opens new member drive', 'Portfolio submission open until next Friday for all trimesters.', 'Student affairs · 5h ago'),
('Academic', 'CGPA recheck form deadline extended', 'Extended by one week due to high volume of requests this trimester.', 'Exam controller · 1d ago');

INSERT INTO events (category, title, description, meta, status, created_by, interest_count) VALUES
('Competition', 'UIU Robotics Fest 2026', 'Inter-university robotics showcase and competition, open to all trimesters.', 'Aug 3 · Auditorium 2', 'approved', 3, 58),
('Academic', 'AI Research Symposium', 'Faculty and student paper presentations on applied machine learning.', 'Aug 6 · Seminar hall', 'approved', 3, 34),
('Club', 'Debate Club open floor', 'Weekly open-floor debate session, all departments welcome.', 'Every Tuesday · Room 402', 'approved', 3, 22),
('Competition', 'Inter-department Chess Cup', 'Knockout format, register in teams of two.', 'Aug 9 · Common room', 'approved', 3, 41);

INSERT INTO events (category, title, description, meta, status, created_by) VALUES
('Club', 'Anime & Manga Club meetup', 'First meetup of the trimester, open discussion + screening.', 'Submitted by Faculty · Club affairs', 'pending', 2);

INSERT INTO achievements (category, title, description, meta) VALUES
('Academic', 'Team UIU wins national hackathon', 'CSE final-year team placed first among 60 universities nationwide.', 'CSE dept · this week'),
('Academic', 'Faculty paper accepted at ICSE 2026', 'Dr. Rahman''s paper on software testing accepted for the main track.', 'CSE faculty');

INSERT INTO research_grants (title, description, status, submitted_by) VALUES
('UGC research grant — applications open', 'Undergraduate research grant up to a fixed ceiling, deadline in 3 days.', 'approved', 3),
('Applied ML for early flood prediction', 'Submitted with two co-authors from EEE department.', 'approved', 2);

INSERT INTO resources (kind, course_code, title, uploader_id, status, downloads) VALUES
('notes', 'CSE 4165', 'Web Programming — Midterm notes', 1, 'approved', 14),
('notes', 'CSE 3521', 'DBMS Lab — Normalization guide', 4, 'approved', 9),
('qbank', 'CSE 4165', 'Midterm question paper — Summer 2025', 3, 'approved', 31),
('qbank', 'CSE 3521', 'Final question paper — Spring 2025', 5, 'approved', 22);

INSERT INTO alerts (type, message) VALUES
('Traffic', 'Heavy traffic near Satarkul Road'),
('Weather', 'Light rain expected this evening around Badda–Satarkul'),
('Campus notice', 'Mid-term routine published for Summer 2026');

INSERT INTO campus_status (id, status) VALUES (1, 'normal');
