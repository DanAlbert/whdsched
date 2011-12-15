CREATE TABLE consultants
(
	id INT NOT NULL AUTO_INCREMENT,
	first_name VARCHAR(40) NOT NULL,
	last_name VARCHAR(40) NOT NULL,
	engr VARCHAR(20) NOT NULL,
	phone VARCHAR(10) NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE shifts
(
	id INT NOT NULL AUTO_INCREMENT,
	start_time TIME,
	end_time TIME,
	location ENUM('WHD', 'Lab', 'KEC', 'Owen'),
	day DATE,
	consultant_id INT,
	PRIMARY KEY (id),
	UNIQUE (start_time, end_time, location, day),
	FOREIGN KEY (consultant_id) REFERENCES consultants (id)
) ENGINE=InnoDB;

CREATE TABLE temp_shifts
(
	id INT NOT NULL AUTO_INCREMENT,
	shift_id INT NOT NULL,
	temp_consultant_id INT NOT NULL,
	post_time TIMESTAMP,
	response_time TIMESTAMP,
	hours SET('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11',
		'12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'),
	assigned_to INT,
	timeout INT,
	PRIMARY KEY (id),
	UNIQUE (shift_id, temp_consultant_id),
	FOREIGN KEY (shift_id) REFERENCES shifts (id),
	FOREIGN KEY (temp_consultant_id) REFERENCES consultants (id),
	FOREIGN KEY (assigned_to) REFERENCES consultants (id)
) ENGINE=InnoDB;
