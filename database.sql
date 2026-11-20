CREATE DATABASE IF NOT EXISTS univibe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE univibe;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  university_id VARCHAR(60) DEFAULT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  category VARCHAR(60) NOT NULL,
  event_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  venue VARCHAR(180) NOT NULL,
  registration_deadline DATE NOT NULL,
  capacity INT NOT NULL DEFAULT 100,
  description TEXT NOT NULL,
  image_url VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  event_id INT NOT NULL,
  attendance TINYINT(1) NOT NULL DEFAULT 0,
  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_registration (user_id, event_id),
  CONSTRAINT fk_reg_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reg_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS event_suggestions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  category VARCHAR(60) NOT NULL,
  event_date DATE NOT NULL,
  venue VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_suggestion_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- The following demo seed is intentionally reset on import so the project always starts with exactly 12 events.
DELETE FROM registrations;
DELETE FROM events;

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('Tech & Innovation Summit','Technology','2026-12-05','10:00:00','14:00:00','Main Auditorium','2026-12-02',180,'Meet student innovators, explore emerging technologies, and hear practical insights from industry speakers. Includes talks, demos, networking and a student project showcase.','assets/images/event-01.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('Campus Music Fest','Cultural','2026-12-12','17:00:00','21:00:00','Sports Ground','2026-12-09',350,'An evening of live performances, student bands, food stalls and campus energy. Bring your friends and enjoy a memorable night.','assets/images/event-02.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('UI/UX Design Workshop','Workshop','2026-12-18','13:00:00','16:00:00','Design Lab 2','2026-12-16',60,'A hands-on workshop covering user research, wireframing, prototyping and presenting a clear design case study. Bring your laptop.','assets/images/event-03.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('Inter-University Coding Challenge','Competition','2026-12-22','09:00:00','15:00:00','Computer Science Block','2026-12-19',120,'Form a team, solve real programming problems and compete with talented students. Prizes and participation certificates will be awarded.','assets/images/event-04.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('AI & Robotics Expo','Technology','2026-12-08','09:30:00','15:00:00','Innovation Lab','2026-12-06',180,'Explore student-built robots, AI demonstrations and emerging technology projects.','assets/images/event-05.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('Winter Charity Carnival','Cultural','2026-12-10','11:00:00','18:00:00','Central Lawn','2026-12-08',300,'Enjoy games, food stalls, student performances and a campus charity drive.','assets/images/event-06.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('Startup Pitch Night','Competition','2026-12-14','16:00:00','19:00:00','Business School Hall','2026-12-12',100,'Pitch your startup idea to mentors and a panel of judges. Get feedback and connect with future collaborators.','assets/images/event-07.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('Photography Walk','Workshop','2026-12-16','10:00:00','13:00:00','University Gardens','2026-12-15',45,'Learn composition and visual storytelling during a guided campus photo walk.','assets/images/event-08.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('Inter-Department Football Cup','Sports','2026-12-20','09:00:00','17:00:00','Sports Complex','2026-12-17',220,'Support your department in a friendly football tournament and cheer for your team.','assets/images/event-09.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('Career & Internship Fair','Seminar','2026-12-23','10:00:00','16:00:00','Main Convention Hall','2026-12-21',400,'Meet recruiters, explore internships and get career advice from professionals.','assets/images/event-10.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('Creative Writing Circle','Workshop','2026-12-27','12:00:00','14:00:00','Library Seminar Room','2026-12-25',50,'Share short stories and poetry, practise creative writing and exchange feedback in a friendly circle.','assets/images/event-11.jpg');

INSERT INTO events (title,category,event_date,start_time,end_time,venue,registration_deadline,capacity,description,image_url) VALUES ('Winter Open Mic','Cultural','2026-12-29','17:00:00','20:00:00','Amphitheatre','2026-12-27',160,'An evening of poetry, acoustic music, comedy and spoken word performances.','assets/images/event-12.jpg');

