/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : new

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2025-07-08 12:40:26
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `committee`
-- ----------------------------
DROP TABLE IF EXISTS `committee`;
CREATE TABLE `committee` (
  `committee_id` int(11) NOT NULL AUTO_INCREMENT,
  `thesis_id` int(11) NOT NULL,
  `prof_id` int(11) NOT NULL,
  `prof_role` enum('Επιβλέπων','Μέλος') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `grade` decimal(3,1) DEFAULT NULL,
  PRIMARY KEY (`committee_id`),
  KEY `thesis_id` (`thesis_id`),
  KEY `professor_id` (`prof_id`),
  CONSTRAINT `committee_ibfk_1` FOREIGN KEY (`thesis_id`) REFERENCES `thesis` (`thesis_id`) ON DELETE CASCADE,
  CONSTRAINT `committee_ibfk_2` FOREIGN KEY (`prof_id`) REFERENCES `professors` (`prof_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of committee
-- ----------------------------
INSERT INTO `committee` VALUES ('35', '29', '1', 'Επιβλέπων', '2025-07-08 10:41:08', null);
INSERT INTO `committee` VALUES ('36', '30', '1', 'Επιβλέπων', '2025-07-08 10:41:31', null);
INSERT INTO `committee` VALUES ('37', '31', '1', 'Επιβλέπων', '2025-07-08 10:41:58', null);
INSERT INTO `committee` VALUES ('38', '32', '1', 'Επιβλέπων', '2025-07-08 10:42:14', '8.0');
INSERT INTO `committee` VALUES ('42', '36', '1', 'Επιβλέπων', '2025-07-08 10:43:21', null);
INSERT INTO `committee` VALUES ('43', '37', '1', 'Επιβλέπων', '2025-07-08 10:43:47', null);
INSERT INTO `committee` VALUES ('44', '38', '2', 'Επιβλέπων', '2025-07-08 10:46:04', null);
INSERT INTO `committee` VALUES ('45', '39', '2', 'Επιβλέπων', '2025-07-08 10:46:25', null);
INSERT INTO `committee` VALUES ('46', '40', '2', 'Επιβλέπων', '2025-07-08 10:46:44', null);
INSERT INTO `committee` VALUES ('47', '41', '2', 'Επιβλέπων', '2025-07-08 10:47:04', null);
INSERT INTO `committee` VALUES ('48', '42', '3', 'Επιβλέπων', '2025-07-08 10:47:59', null);
INSERT INTO `committee` VALUES ('49', '43', '3', 'Επιβλέπων', '2025-07-08 10:48:15', null);
INSERT INTO `committee` VALUES ('50', '44', '3', 'Επιβλέπων', '2025-07-08 10:48:39', null);
INSERT INTO `committee` VALUES ('53', '45', '2', 'Επιβλέπων', '2025-07-08 11:22:07', null);
INSERT INTO `committee` VALUES ('54', '45', '1', 'Μέλος', '2025-07-08 11:23:20', null);
INSERT INTO `committee` VALUES ('55', '36', '5', 'Μέλος', '2025-07-08 11:26:03', null);
INSERT INTO `committee` VALUES ('56', '36', '6', 'Μέλος', '2025-07-08 11:26:19', null);
INSERT INTO `committee` VALUES ('57', '46', '1', 'Επιβλέπων', '2025-07-08 11:34:54', null);
INSERT INTO `committee` VALUES ('58', '47', '1', 'Επιβλέπων', '2025-07-08 11:39:05', null);
INSERT INTO `committee` VALUES ('59', '48', '1', 'Επιβλέπων', '2025-07-08 11:39:49', null);
INSERT INTO `committee` VALUES ('60', '49', '2', 'Επιβλέπων', '2025-07-08 11:40:36', null);
INSERT INTO `committee` VALUES ('61', '50', '2', 'Επιβλέπων', '2025-07-08 11:40:55', null);
INSERT INTO `committee` VALUES ('62', '51', '3', 'Επιβλέπων', '2025-07-08 11:42:03', null);
INSERT INTO `committee` VALUES ('63', '32', '2', 'Μέλος', '2025-07-08 11:44:58', '7.0');
INSERT INTO `committee` VALUES ('64', '32', '3', 'Μέλος', '2025-07-08 11:45:10', '8.0');
INSERT INTO `committee` VALUES ('65', '31', '2', 'Μέλος', '2025-07-08 12:11:59', '9.0');
INSERT INTO `committee` VALUES ('66', '31', '3', 'Μέλος', '2025-07-08 12:12:13', null);
INSERT INTO `committee` VALUES ('67', '52', '1', 'Επιβλέπων', '2025-07-08 12:36:16', null);
INSERT INTO `committee` VALUES ('68', '53', '1', 'Επιβλέπων', '2025-07-08 12:37:03', null);
INSERT INTO `committee` VALUES ('69', '54', '1', 'Επιβλέπων', '2025-07-08 12:37:17', null);
INSERT INTO `committee` VALUES ('70', '55', '1', 'Επιβλέπων', '2025-07-08 12:37:37', null);
INSERT INTO `committee` VALUES ('71', '56', '1', 'Επιβλέπων', '2025-07-08 12:37:53', null);
INSERT INTO `committee` VALUES ('72', '57', '2', 'Επιβλέπων', '2025-07-08 12:38:26', null);
INSERT INTO `committee` VALUES ('73', '58', '2', 'Επιβλέπων', '2025-07-08 12:38:43', null);
INSERT INTO `committee` VALUES ('74', '59', '2', 'Επιβλέπων', '2025-07-08 12:38:55', null);

-- ----------------------------
-- Table structure for `invitations`
-- ----------------------------
DROP TABLE IF EXISTS `invitations`;
CREATE TABLE `invitations` (
  `invitation_id` int(11) NOT NULL AUTO_INCREMENT,
  `thesis_id` int(11) NOT NULL,
  `prof_id` int(11) NOT NULL,
  `invited_prof_id` int(11) NOT NULL,
  `role` enum('Επιβλέπων','Μέλος') NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`invitation_id`),
  KEY `thesis_id` (`thesis_id`),
  KEY `prof_id` (`prof_id`),
  KEY `invited_prof_id` (`invited_prof_id`),
  CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`thesis_id`) REFERENCES `thesis` (`thesis_id`),
  CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`prof_id`) REFERENCES `professors` (`prof_id`),
  CONSTRAINT `invitations_ibfk_3` FOREIGN KEY (`invited_prof_id`) REFERENCES `professors` (`prof_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of invitations
-- ----------------------------
INSERT INTO `invitations` VALUES ('41', '45', '2', '1', 'Μέλος', 'accepted', '2025-07-08 11:23:20', '2025-07-08 11:23:08');
INSERT INTO `invitations` VALUES ('42', '45', '2', '3', 'Μέλος', 'pending', null, '2025-07-08 11:23:08');
INSERT INTO `invitations` VALUES ('43', '36', '1', '5', 'Μέλος', 'accepted', '2025-07-08 11:26:03', '2025-07-08 11:25:36');
INSERT INTO `invitations` VALUES ('44', '36', '1', '6', 'Μέλος', 'accepted', '2025-07-08 11:26:19', '2025-07-08 11:25:36');
INSERT INTO `invitations` VALUES ('45', '51', '3', '1', 'Μέλος', 'pending', null, '2025-07-08 11:42:55');
INSERT INTO `invitations` VALUES ('46', '51', '3', '2', 'Μέλος', 'pending', null, '2025-07-08 11:42:55');
INSERT INTO `invitations` VALUES ('47', '32', '1', '2', 'Μέλος', 'accepted', '2025-07-08 11:44:58', '2025-07-08 11:44:49');
INSERT INTO `invitations` VALUES ('48', '32', '1', '3', 'Μέλος', 'accepted', '2025-07-08 11:45:10', '2025-07-08 11:44:49');
INSERT INTO `invitations` VALUES ('49', '31', '1', '2', 'Μέλος', 'accepted', '2025-07-08 12:11:59', '2025-07-08 12:11:47');
INSERT INTO `invitations` VALUES ('50', '31', '1', '3', 'Μέλος', 'accepted', '2025-07-08 12:12:13', '2025-07-08 12:11:47');

-- ----------------------------
-- Table structure for `notes`
-- ----------------------------
DROP TABLE IF EXISTS `notes`;
CREATE TABLE `notes` (
  `note_id` int(11) NOT NULL AUTO_INCREMENT,
  `thesis_id` int(11) NOT NULL,
  `prof_id` int(11) NOT NULL,
  `notes` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`note_id`),
  KEY `thesis_id` (`thesis_id`),
  KEY `prof_id` (`prof_id`),
  CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`thesis_id`) REFERENCES `thesis` (`thesis_id`) ON DELETE CASCADE,
  CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`prof_id`) REFERENCES `professors` (`prof_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of notes
-- ----------------------------

-- ----------------------------
-- Table structure for `professors`
-- ----------------------------
DROP TABLE IF EXISTS `professors`;
CREATE TABLE `professors` (
  `prof_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`prof_id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `professors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of professors
-- ----------------------------
INSERT INTO `professors` VALUES ('1', '1', 'Παναγιώτης', 'Παπαδόπουλος', 'Πληροφορικής', 'p.papadopoulos@university.edu', '2101234567');
INSERT INTO `professors` VALUES ('2', '2', 'Αλέξανδρος', 'Αλεξόπουλος', 'Πληροφορικής', 'a.alexopoulos@university.edu', '2109876543');
INSERT INTO `professors` VALUES ('3', '3', 'Νικόλαος', 'Κουτσούμπας', 'Ηλεκτρολόγων Μηχανικών και Μηχανικών Υπολογιστών', 'n.koutsoubas@university.edu', '2101234001');
INSERT INTO `professors` VALUES ('4', '4', 'Μαρία', 'Αντωνίου', 'Πληροφορικής', 'm.antoniou@university.edu', '2101234002');
INSERT INTO `professors` VALUES ('5', '5', 'Δημήτρης', 'Στεφανίδης', 'Ηλεκτρολόγων Μηχανικών και Μηχανικών Υπολογιστών', 'd.stefanidis@university.edu', '2101234003');
INSERT INTO `professors` VALUES ('6', '6', 'Ελένη', 'Οικονόμου', 'Μαθηματικών', 'e.oikonomou@university.edu', '2101234004');

-- ----------------------------
-- Table structure for `secretary`
-- ----------------------------
DROP TABLE IF EXISTS `secretary`;
CREATE TABLE `secretary` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `specialization` enum('Προπτυχιακό Πρόγραμμα Σπουδών','Μεταπτυχιακό','Διδακτορικό') NOT NULL,
  PRIMARY KEY (`member_id`),
  KEY `user_id_secretary` (`user_id`),
  CONSTRAINT `user_id_secretary` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of secretary
-- ----------------------------
INSERT INTO `secretary` VALUES ('17', '17', 'Αγγελική', 'Καραθανάση', '2101234005', 'Μεταπτυχιακό');

-- ----------------------------
-- Table structure for `students`
-- ----------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `AM` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `mobile_phone` varchar(20) DEFAULT NULL,
  `landline_phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `AM` (`AM`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1027 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of students
-- ----------------------------
INSERT INTO `students` VALUES ('7', '7', '123462', 'Ανδρέας', 'Παπαδόπουλος', null, 'andreas.papadopoulos@university.edu', '6971234573', null);
INSERT INTO `students` VALUES ('8', '8', '123463', 'Σοφία', 'Μιχαηλίδου', 'Οδός Ερμού 12, Αθήνα', 'sofia.michailidou@university.edu', '6971234574', null);
INSERT INTO `students` VALUES ('9', '9', '123464', 'Γιάννης', 'Κωνσταντίνου', null, 'giannis.konstantinou@university.edu', '6971234575', null);
INSERT INTO `students` VALUES ('10', '10', '123465', 'Ειρήνη', 'Δημητρίου', null, 'irini.dimitriou@university.edu', '6971234576', null);
INSERT INTO `students` VALUES ('11', '11', '123466', 'Νίκος', 'Παυλίδης', null, 'nikos.pavlidis@university.edu', '6971234577', null);
INSERT INTO `students` VALUES ('12', '12', '123467', 'Αθηνά', 'Παπανικολάου', null, 'athina.papanikolaou@university.edu', '6971234578', null);
INSERT INTO `students` VALUES ('13', '13', '123468', 'Δημήτρης', 'Αλεξάνδρου', null, 'dimitris.alexandrou@university.edu', '6971234579', null);
INSERT INTO `students` VALUES ('14', '14', '123469', 'Χρύσα', 'Παπαδοπούλου', null, 'chrysa.papadopoulou@university.edu', '6971234580', null);
INSERT INTO `students` VALUES ('15', '15', '123470', 'Μιχάλης', 'Γεωργίου', null, 'michalis.georgiou@university.edu', '6971234581', null);
INSERT INTO `students` VALUES ('16', '16', '123471', 'Μαρία', 'Ιωάννου', null, 'maria.ioannou@university.edu', '6971234582', null);
INSERT INTO `students` VALUES ('1017', '217', '123472', 'Δημήτρης', 'Παπαγεωργίου', null, 'dimitris.papageorgiou@university.edu', '6971234583', null);
INSERT INTO `students` VALUES ('1018', '218', '123473', 'Αναστασία', 'Κωνσταντίνου', 'Οδός Σόλωνος 5, Αθήνα', 'anastasia.konstantinou@university.edu', '6971234584', null);
INSERT INTO `students` VALUES ('1019', '219', '123474', 'Σπύρος', 'Ιωάννου', null, 'spyros.ioannou@university.edu', '6971234585', null);
INSERT INTO `students` VALUES ('1020', '220', '123475', 'Ελένη', 'Αντωνίου', null, 'eleni.antoniou@university.edu', '6971234586', null);
INSERT INTO `students` VALUES ('1021', '221', '123476', 'Γιώργος', 'Δημητρίου', null, 'georgios.dimitriou@university.edu', '6971234587', null);
INSERT INTO `students` VALUES ('1022', '502', '123456', 'Μαρία', 'Παπαδοπούλου', 'Οδός Ελένης 15, Αθήνα', 'mpapadopoulou@email.com', '6912345678', '2101234567');
INSERT INTO `students` VALUES ('1023', '503', '123457', 'Γιάννης', 'Ιωάννου', 'Οδός Σόλωνος 42, Θεσσαλονίκη', 'giannis.ioannou@email.com', '6912345679', '2310123456');
INSERT INTO `students` VALUES ('1024', '504', '123458', 'Ελένη', 'Κωνσταντίνου', 'Οδός Αριστοτέλους 8, Πάτρα', 'eleni.konstantinou@email.com', '6912345680', '2610123456');
INSERT INTO `students` VALUES ('1025', '505', '123459', 'Δημήτρης', 'Αντωνίου', 'Οδός Πλαστήρα 33, Ηράκλειο', 'dimitris.antoniou@email.com', '6912345681', '2810123456');
INSERT INTO `students` VALUES ('1026', '506', '123460', 'Σοφία', 'Μιχαήλ', 'Οδός Κρήτης 12, Βόλος', 'sofia.michael@email.com', '6912345682', '2421012345');

-- ----------------------------
-- Table structure for `thesis`
-- ----------------------------
DROP TABLE IF EXISTS `thesis`;
CREATE TABLE `thesis` (
  `thesis_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Διαθέσιμη','Υπό Ανάθεση','Ενεργή','Aκυρωμένη','Περατωμένη','Υπό Εξέταση') NOT NULL DEFAULT 'Διαθέσιμη',
  `student_id` int(11) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `prof_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pdf_file` varchar(255) DEFAULT NULL,
  `degree` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `assignment_cancelled` tinyint(1) DEFAULT 0,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `final_grade` decimal(3,1) DEFAULT NULL,
  `final_report_file` varchar(255) DEFAULT NULL,
  `examination_minutes_file` varchar(255) DEFAULT NULL,
  `draft_file` varchar(255) DEFAULT NULL,
  `external_links` text DEFAULT NULL,
  `examination_date` date DEFAULT NULL,
  `examination_time` time DEFAULT NULL,
  `examination_mode` enum('Δια ζώσης','Διαδικτυακά') DEFAULT NULL,
  `room_or_link` varchar(255) DEFAULT NULL,
  `repository_link` varchar(255) DEFAULT NULL,
  `html_minutes_file` text DEFAULT NULL,
  PRIMARY KEY (`thesis_id`),
  KEY `fk_thesis_professor` (`prof_id`),
  KEY `fk_thesis_student` (`student_id`),
  KEY `fk_thesis_created_by` (`created_by`),
  CONSTRAINT `fk_thesis_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_thesis_professor` FOREIGN KEY (`prof_id`) REFERENCES `professors` (`prof_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_thesis_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of thesis
-- ----------------------------
INSERT INTO `thesis` VALUES ('29', ' Τεχνητή Νοημοσύνη & Machine Learning', 'Σύστημα πρόβλεψης καρδιακών παθήσεων με χρήση Deep Learning και ιατρικών δεδομένων', 'Διαθέσιμη', null, null, '1', '2025-07-08 10:41:08', '2025-07-08 10:41:08', null, null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('30', 'Data Science & Big Data', 'Πρόγνωση τιμών μετοχών με χρήση χρονοσειρών (ARIMA, LSTM) και δεδομένων από το Yahoo Finance.', 'Διαθέσιμη', null, null, '1', '2025-07-08 10:41:31', '2025-07-08 10:41:31', 'uploads/686ccbaba2c1d_Sample Pdf.pdf', null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('31', 'Cybersecurity', 'Ανίχνευση DDoS επιθέσεων σε δίκτυα με χρήση ML', 'Υπό Εξέταση', '14', '2025-07-08 12:11:25', '1', '2025-07-08 12:12:35', '2025-07-08 12:12:35', null, null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('32', ' Blockchain & FinTech', 'Έξυπνο συμβόλαιο (smart contract) για ψηφιακές ψηφοφορίες', 'Περατωμένη', '7', '2025-07-08 11:44:02', '1', '2025-07-08 12:04:56', '2025-07-08 12:04:56', null, null, null, null, '1', null, '0', null, null, '2025-07-08 12:04:56', '8.0', null, null, null, 'https://www.kaggle.com/datasets/example/stock-data\', \n    \'2025-12-15\'', null, null, null, null, 'https://github.com/example/stock-prediction', null);
INSERT INTO `thesis` VALUES ('33', 'IoT & Ενσωματωμένα Συστήματα', 'Έξυπνο σπίτι (smart home) με αισθητήρες και αυτοματοποίηση', 'Aκυρωμένη', null, '2025-07-08 12:08:52', '1', '2025-07-08 12:31:53', '2025-07-08 12:31:53', null, null, null, null, '1', 'Ακύρωση από καθηγητή', '0', '2025-07-08 12:31:36', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('34', ' Computer Vision & Robotics', 'Αναγνώριση αντικειμένων για αυτόνομα οχήματα με YOLO και OpenCV.', 'Aκυρωμένη', null, '2025-07-08 10:49:54', '1', '2025-07-08 12:30:57', '2025-07-08 12:30:57', null, null, null, null, '1', 'Ακύρωση από καθηγητή', '0', '2025-07-08 12:30:41', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('35', ' Cloud Computing & DevOps', 'Ανάπτυξη serverless εφαρμογής για επεξεργασία εικόνας (AWS Lambda, Python).', 'Aκυρωμένη', null, '2025-07-08 10:49:48', '1', '2025-07-08 12:31:02', '2025-07-08 12:31:02', null, null, null, null, '1', 'Ακύρωση από καθηγητή', '0', '2025-07-08 12:26:58', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('36', 'Ανάπτυξη Εφαρμογών', 'Εφαρμογή διαχείρισης tasks με AI', 'Υπό Ανάθεση', '1020', '2025-07-08 12:06:42', '1', '2025-07-08 12:06:42', '2025-07-08 12:06:42', null, null, null, null, '1', 'Ακύρωση από Διδάσκοντα', '0', '2025-07-08 11:30:45', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('37', 'Θεωρητική Πληροφορική', 'Βελτιστοποίηση αλγορίθμων δρομολόγησης για διανομή τροφίμων (Genetic Algorithms).', 'Διαθέσιμη', null, '2025-07-08 10:49:31', '1', '2025-07-08 10:51:35', '2025-07-08 10:51:35', null, null, null, null, '1', 'Ακύρωση από Διδάσκοντα', '0', '2025-07-08 10:51:35', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('38', 'Ανίχνευση Επιθέσεων σε Έξυπνους Θερμοστάτες', 'Εφαρμογή IDS (Intrusion Detection System) για IoT συσκευές με χρήση Raspberry Pi και Kali Linux.', 'Διαθέσιμη', null, null, '2', '2025-07-08 10:46:04', '2025-07-08 10:46:04', null, null, null, null, '2', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('39', 'Ανώνυμο Σύστημα Ψηφοφορίας με Blockchain', 'Δημιουργία DApp (Decentralized Application) για ψηφοφορίες με χρήση Ethereum και Solidity', 'Υπό Ανάθεση', '9', '2025-07-08 10:50:23', '2', '2025-07-08 10:50:23', '2025-07-08 10:50:23', null, null, null, null, '2', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('40', 'Αναγνώριση Πινακίδων Αυτοκινήτων με YOLOv8', 'Υλοποίηση συστήματος αναγνώρισης πινακίδων σε πραγματικό χρόνο με Python και OpenCV.', 'Υπό Ανάθεση', '8', '2025-07-08 10:50:18', '2', '2025-07-08 10:50:18', '2025-07-08 10:50:18', 'uploads/686ccce4dfd6f_Sample Pdf.pdf', null, null, null, '2', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('41', 'Πρόβλεψη Αποτελεσμάτων Πρωταθλήματος Ποδοσφαίρου', 'Χρήση regression models και feature engineering σε datasets από την Super League.', 'Διαθέσιμη', null, null, '2', '2025-07-08 10:47:04', '2025-07-08 10:47:04', null, null, null, null, '2', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('42', 'Εφαρμογή Επαυξημένης Πραγματικότητας για Εκθέματα Μουσείου', 'Ανάπτυξη εφαρμογής με Unity και ARCore για διαδραστική περιήγηση σε μουσειακούς χώρους.', 'Διαθέσιμη', null, null, '3', '2025-07-08 10:47:59', '2025-07-08 10:47:59', null, null, null, null, '3', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('43', 'Σύστημα Διαχείρισης Projects με Χρήση Microservices', 'Ανάπτυξη πλατφόρμας με Spring Boot, Docker και Kubernetes.', 'Υπό Ανάθεση', '11', '2025-07-08 10:50:46', '3', '2025-07-08 10:50:46', '2025-07-08 10:50:46', null, null, null, null, '3', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('44', 'Έξυπνο Σύστημα Συναρμολόγησης με Ρομποτικό Βραχίονα', 'Έξυπνο Σύστημα Συναρμολόγησης με Ρομποτικό Βραχίονα', 'Υπό Ανάθεση', '15', '2025-07-08 10:50:41', '3', '2025-07-08 10:50:41', '2025-07-08 10:50:41', null, null, null, null, '3', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('45', 'Αυτοματοποιημένο Σύστημα Αξιολόγησης Πτυχιακών Εργασιών', 'Εφαρμογή για ανίχνευση λογικών σφαλμάτων σε κώδικα Java με χρήση static analysis.', 'Ενεργή', '16', '2025-07-08 11:22:26', '2', '2025-07-08 11:23:20', '2025-07-08 11:23:20', null, null, null, null, '2', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('46', 'Εφαρμογή Quantum Algorithms σε Προβλήματα Logistics', 'Μελέτη του αλγορίθμου του Grover για βελτιστοποίηση διαδρομών παράδοσης.', 'Διαθέσιμη', null, null, '1', '2025-07-08 11:34:54', '2025-07-08 11:34:54', null, null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('47', 'Σύστημα Προσωπικοποιημένης Μάθησης με Reinforcement Learning', 'Ανάπτυξη συστήματος που προσαρμόζει το εκπαιδευτικό περιεχόμενο βάσει συμπεριφοράς μαθητή (Python, TensorFlow)', 'Διαθέσιμη', null, null, '1', '2025-07-08 11:39:05', '2025-07-08 11:39:05', null, null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('48', 'Ανάλυση Ευπαθειών σε DAO (Decentralized Autonomous Organizations)', 'Μελέτη κρίσιμων αδυναμιών σε έξυπνα συμβόλαια DAO και προτάσεις βελτίωσης.', 'Διαθέσιμη', null, null, '1', '2025-07-08 11:39:49', '2025-07-08 11:39:49', 'uploads/686cd955b1295_Sample Pdf.pdf', null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('49', 'Έξυπνο Σύστημα Άρδευσης με Αισθητήρες Εδάφους', 'Παρακολούθηση υγρασίας εδάφους και αυτόματη άρδευση με χρήση Arduino και IoT πλατφόρμας.', 'Διαθέσιμη', null, null, '2', '2025-07-08 11:40:36', '2025-07-08 11:40:36', null, null, null, null, '2', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('50', 'Πρόγνωση Ποιότητας Αέρα με Χρήση Μεγάλων Δεδομένων', 'Ανάλυση δεδομένων από αισθητήρες και πρόγνωση ρύπων με χρήση χρονοσειρών (Python, Pandas).', 'Διαθέσιμη', null, null, '2', '2025-07-08 11:40:55', '2025-07-08 11:40:55', 'uploads/686cd99785bf1_Sample Pdf.pdf', null, null, null, '2', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('51', 'Εφαρμογή Επαυξημένης Πραγματικότητας για Εκπαίδευση Μηχανικών', 'Ανάπτυξη εφαρμογής με Unity και Microsoft HoloLens για προβολή 3D μοντέλων μηχανημάτων.', 'Υπό Ανάθεση', '1018', '2025-07-08 11:42:28', '3', '2025-07-08 11:42:28', '2025-07-08 11:42:28', 'uploads/686cd9db2bbad_Sample Pdf.pdf', null, null, null, '3', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('52', 'Ανάπτυξη Συστήματος Διαχείρισης Διπλωματικών', 'Σχεδίαση και υλοποίηση web εφαρμογής για διαχείριση διπλωματικών εργασιών με PHP και MySQL', 'Διαθέσιμη', null, null, '1', '2025-07-08 12:36:16', '2025-07-08 12:36:16', null, null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('53', 'Ανάλυση Μεγάλων Δεδομένων για Εξατομικευμένη Ιατρική', 'Εξόρυξη γνώσης από ιατρικά δεδομένα με τεχνικές big data analytics', 'Διαθέσιμη', null, null, '1', '2025-07-08 12:37:03', '2025-07-08 12:37:03', null, null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('54', 'Ασφάλεια σε IoT Συσκευές', 'Μελέτη και βελτίωση ασφάλειας σε συσκευές Internet of Things', 'Διαθέσιμη', null, null, '1', '2025-07-08 12:37:17', '2025-07-08 12:37:17', null, null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('55', 'Εφαρμογή Εξομοιωτή Αυτόνομων Οχημάτων', 'Ανάπτυξη εφαρμογής προσομοίωσης αυτόνομων οχημάτων με Unity', 'Διαθέσιμη', null, null, '1', '2025-07-08 12:37:37', '2025-07-08 12:37:37', 'uploads/686ce6e106c5f_Sample Pdf.pdf', null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('56', 'Σύστημα Αναγνώρισης Προσώπου για Έλεγχο Πρόσβασης', 'Ανάπτυξη συστήματος βιομετρικής ταυτοποίησης με OpenCV', 'Διαθέσιμη', null, null, '1', '2025-07-08 12:37:53', '2025-07-08 12:37:53', null, null, null, null, '1', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('57', 'Εφαρμογή Ψηφιακής Βιβλιοθήκης με Django', 'Δημιουργία πλατφόρμας διαχείρισης ψηφιακών βιβλίων', 'Διαθέσιμη', null, null, '2', '2025-07-08 12:38:26', '2025-07-08 12:38:26', null, null, null, null, '2', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('58', 'Ανάλυση Συναισθημάτων στα Κοινωνικά Δίκτυα', 'Μελέτη γνώμης χρηστών σε κοινωνικά δίκτυα με φυσική επεξεργασία γλώσσας', 'Διαθέσιμη', null, null, '2', '2025-07-08 12:38:43', '2025-07-08 12:38:43', null, null, null, null, '2', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `thesis` VALUES ('59', 'Σύστημα Συνεργατικής Φιλτραρισμένης Σύστασης', 'Αλγόριθμοι συνεργατικού φιλτραρίσματος για συστήματα σύστασης', 'Διαθέσιμη', null, null, '2', '2025-07-08 12:38:55', '2025-07-08 12:38:55', null, null, null, null, '2', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null);

-- ----------------------------
-- Table structure for `thesis_status_history`
-- ----------------------------
DROP TABLE IF EXISTS `thesis_status_history`;
CREATE TABLE `thesis_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thesis_id` int(11) NOT NULL,
  `old_status` enum('Διαθέσιμη','Υπό Ανάθεση','Ενεργή','Aκυρωμένη','Περατωμένη','Υπό Εξέταση') DEFAULT NULL,
  `new_status` enum('Διαθέσιμη','Υπό Ανάθεση','Ενεργή','Aκυρωμένη','Περατωμένη','Υπό Εξέταση') DEFAULT NULL,
  `changed_by` int(11) NOT NULL COMMENT 'user_id',
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thesis_id` (`thesis_id`),
  CONSTRAINT `thesis_status_history_ibfk_1` FOREIGN KEY (`thesis_id`) REFERENCES `thesis` (`thesis_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of thesis_status_history
-- ----------------------------
INSERT INTO `thesis_status_history` VALUES ('20', '37', 'Διαθέσιμη', 'Υπό Ανάθεση', '1', '2025-07-08 10:49:31', null);
INSERT INTO `thesis_status_history` VALUES ('21', '36', 'Διαθέσιμη', 'Υπό Ανάθεση', '1', '2025-07-08 10:49:42', null);
INSERT INTO `thesis_status_history` VALUES ('22', '35', 'Διαθέσιμη', 'Υπό Ανάθεση', '1', '2025-07-08 10:49:48', null);
INSERT INTO `thesis_status_history` VALUES ('23', '34', 'Διαθέσιμη', 'Υπό Ανάθεση', '1', '2025-07-08 10:49:54', null);
INSERT INTO `thesis_status_history` VALUES ('24', '33', 'Διαθέσιμη', 'Υπό Ανάθεση', '1', '2025-07-08 10:50:01', null);
INSERT INTO `thesis_status_history` VALUES ('25', '40', 'Διαθέσιμη', 'Υπό Ανάθεση', '2', '2025-07-08 10:50:18', null);
INSERT INTO `thesis_status_history` VALUES ('26', '39', 'Διαθέσιμη', 'Υπό Ανάθεση', '2', '2025-07-08 10:50:23', null);
INSERT INTO `thesis_status_history` VALUES ('27', '44', 'Διαθέσιμη', 'Υπό Ανάθεση', '3', '2025-07-08 10:50:41', null);
INSERT INTO `thesis_status_history` VALUES ('28', '43', 'Διαθέσιμη', 'Υπό Ανάθεση', '3', '2025-07-08 10:50:46', null);
INSERT INTO `thesis_status_history` VALUES ('29', '37', 'Υπό Ανάθεση', 'Διαθέσιμη', '1', '2025-07-08 10:51:35', null);
INSERT INTO `thesis_status_history` VALUES ('30', '45', 'Διαθέσιμη', 'Υπό Ανάθεση', '2', '2025-07-08 11:22:26', null);
INSERT INTO `thesis_status_history` VALUES ('31', '33', 'Υπό Εξέταση', 'Διαθέσιμη', '1', '2025-07-08 11:28:45', null);
INSERT INTO `thesis_status_history` VALUES ('32', '36', 'Υπό Εξέταση', 'Διαθέσιμη', '1', '2025-07-08 11:30:45', null);
INSERT INTO `thesis_status_history` VALUES ('33', '51', 'Διαθέσιμη', 'Υπό Ανάθεση', '3', '2025-07-08 11:42:28', null);
INSERT INTO `thesis_status_history` VALUES ('34', '32', 'Διαθέσιμη', 'Υπό Ανάθεση', '1', '2025-07-08 11:44:02', null);
INSERT INTO `thesis_status_history` VALUES ('35', '36', 'Διαθέσιμη', 'Υπό Ανάθεση', '1', '2025-07-08 12:06:42', null);
INSERT INTO `thesis_status_history` VALUES ('36', '33', 'Διαθέσιμη', 'Υπό Ανάθεση', '1', '2025-07-08 12:08:52', null);
INSERT INTO `thesis_status_history` VALUES ('37', '31', 'Διαθέσιμη', 'Υπό Ανάθεση', '1', '2025-07-08 12:11:25', null);
INSERT INTO `thesis_status_history` VALUES ('38', '35', 'Ενεργή', '', '1', '2025-07-08 12:26:58', 'Ακύρωση από καθηγητή');
INSERT INTO `thesis_status_history` VALUES ('39', '34', 'Ενεργή', '', '1', '2025-07-08 12:30:41', 'Ακύρωση από καθηγητή');
INSERT INTO `thesis_status_history` VALUES ('40', '33', 'Ενεργή', '', '1', '2025-07-08 12:31:36', 'Ακύρωση από καθηγητή');

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('professor','student','secretary') NOT NULL,
  `password` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('1', 'professor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ppapadopoulos');
INSERT INTO `users` VALUES ('2', 'professor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alexopoulos');
INSERT INTO `users` VALUES ('3', 'professor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nkoutsou');
INSERT INTO `users` VALUES ('4', 'professor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mantoniou');
INSERT INTO `users` VALUES ('5', 'professor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dstefanidis');
INSERT INTO `users` VALUES ('6', 'professor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'eoikonomou');
INSERT INTO `users` VALUES ('7', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'apapadopoulos');
INSERT INTO `users` VALUES ('8', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'smichailidou');
INSERT INTO `users` VALUES ('9', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gkonstantinou');
INSERT INTO `users` VALUES ('10', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'idimitriou');
INSERT INTO `users` VALUES ('11', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'npavlidis');
INSERT INTO `users` VALUES ('12', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'apapanikolaou');
INSERT INTO `users` VALUES ('13', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dalexandrou');
INSERT INTO `users` VALUES ('14', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cpapadopoulou');
INSERT INTO `users` VALUES ('15', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mgeorgiou');
INSERT INTO `users` VALUES ('16', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mioannou');
INSERT INTO `users` VALUES ('17', 'secretary', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretary3');
INSERT INTO `users` VALUES ('217', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dpapageorgiou');
INSERT INTO `users` VALUES ('218', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'akonstantinou');
INSERT INTO `users` VALUES ('219', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sioannou');
INSERT INTO `users` VALUES ('220', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'eantoniou');
INSERT INTO `users` VALUES ('221', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gdimtriou');
INSERT INTO `users` VALUES ('502', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student1');
INSERT INTO `users` VALUES ('503', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student2');
INSERT INTO `users` VALUES ('504', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student3');
INSERT INTO `users` VALUES ('505', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student4');
INSERT INTO `users` VALUES ('506', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student5');
