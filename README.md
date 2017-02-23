# Hospital Management System Mini-Project

This **was my first short PHP project** for Web Technology subject created in October 2016. 

## Features:
  1. Front Page Slideshow
  2. Login / Logout for customer.
  3. Seperate login for admin (location/hms-admin) - username: admin, password: admin
  4. Navigation Bar
  5. Ability to Add patient detail and book appointment.
  6. CSS using Twitter Bootstrap
  
## Screenshots:

### Homescreen Image:
![Homescreen Image](https://github.com/ankschoubey/hospital-management-system-php-mysql/blob/master/readme-images/index.png?raw=true)

### Add Patient:
![Add Patient Image](https://github.com/ankschoubey/hospital-management-system-php-mysql/blob/master/readme-images/add-patient.png?raw=true)

### Admin/Staff Login:
![Admin/Staff Login Image](https://github.com/ankschoubey/hospital-management-system-php-mysql/blob/master/readme-images/staff-login.png?raw=true)

### Admin Panel:
![Admin Panel Image](https://github.com/ankschoubey/hospital-management-system-php-mysql/blob/master/readme-images/hms-admin.png?raw=true)

### View All Appointments:
![View All Appointments Image](https://github.com/ankschoubey/hospital-management-system-php-mysql/blob/master/readme-images/all-appointments.png?raw=true)

### Update Information:
![Update Information Image](https://github.com/ankschoubey/hospital-management-system-php-mysql/blob/master/readme-images/update-patient-info.png?raw=true)
  
**Add this to MySQL**
```SQL
CREATE TABLE `admin` (
  `email` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `appointments` (
  `appointment_no` int(30) NOT NULL,
  `patient_id` int(30) NOT NULL,
  `speciality` varchar(30) NOT NULL,
  `medical_condition` text,
  `doctors_suggestion` varchar(30) DEFAULT NULL,
  `payment_amount` int(199) DEFAULT NULL,
  `case_closed` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `clerks` (
  `email` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `fullname` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='stores information about clerk';

CREATE TABLE `doctors` (
  `email` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `fullname` varchar(30) NOT NULL,
  `speciality` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `patient_info` (
  `patient_Id` int(20) NOT NULL,
  `full_name` varchar(20) NOT NULL,
  `DOB` int(10) NOT NULL,
  `weight` int(8) NOT NULL,
  `phone_no` varchar(30) NOT NULL,
  `address` varchar(260) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='patient';

CREATE TABLE `users` (
  `email` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `fullname` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_no`);

ALTER TABLE `clerks`
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `doctors`
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `patient_info`
  ADD PRIMARY KEY (`patient_Id`);

ALTER TABLE `users`
  ADD UNIQUE KEY `username` (`email`);


ALTER TABLE `appointments`
  MODIFY `appointment_no` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;
ALTER TABLE `patient_info`
  MODIFY `patient_Id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;
```

Copyright (c) 2017 Ankush Choubey - MIT License
