CREATE TABLE consultants
(
	id INT NOT NULL AUTO_INCREMENT,
	first_name VARCHAR(40) NOT NULL,
	last_name VARCHAR(40) NOT NULL,
	engr VARCHAR(20) NOT NULL,
	phone VARCHAR(10) NOT NULL,
	admin BOOLEAN NOT NULL DEFAULT 0,
	PRIMARY KEY (id),
	UNIQUE (engr)
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
	FOREIGN KEY (consultant_id) REFERENCES consultants (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE temp_shifts
(
	id INT NOT NULL AUTO_INCREMENT,
	shift_id INT NOT NULL,
	temp_consultant_id INT,
	post_time TIMESTAMP NOT NULL DEFAULT NOW(),
	response_time TIMESTAMP,
	assigned_to INT,
	timeout INT,
	PRIMARY KEY (id),
	UNIQUE (shift_id),
	FOREIGN KEY (shift_id) REFERENCES shifts (id) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (temp_consultant_id) REFERENCES consultants (id) ON DELETE SET NULL ON UPDATE CASCADE,
	FOREIGN KEY (assigned_to) REFERENCES consultants (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE terms
(
	id INT NOT NULL,
	term ENUM('Summer', 'Fall', 'Winter', 'Spring') NOT NULL,
	year INT NOT NULL,
	start_date DATE NOT NULL,
	end_date DATE NOT NULL,
	PRIMARY KEY (id),
	UNIQUE (term, year)
) ENGINE=InnoDB;
