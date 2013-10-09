<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();



// Insert Users
$i = 1001;
$db->insertCoolUser(
    $i, //id
    'Bob', // Name
    'Mansfield', // Surname
    'mansfield@forbes.com', // Email
    '82', // Industry
    '3', // Goal
    '512f3fd0d86c1159450ba1ee', // Foursquare
    'Yolk', // Place
    'Just humble art director', // Summary
    $i.'.png', // Photo
    '380933222233', // Phone
    '50.44008299439331', // Lat
    '30.511329174041744', // Lng
    'mansfield', // Skype
    '5', // Rating
    '31', // Experience
    'CjQwAAAA2-HDsdQ_XpT6uDCjb6ofXs2RvBhIvQ--qMsnADlBFqWfSAOsCmUG_rmUG-ygHe69EhAFFCvKUfy-aXMTj8xiDtkxGhRKt2mmPZZUXYzqieEyn1l4eA7DoA', // City
    'Kiev, UA' // City name
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Art director', // Position name
    'Forbes Magazine', // Company
    '1', // Current
    '1982-08-01', // Start Time
    'null', // End Time
    0
);


// Add Education
$db->addJob(
    $i, // ID
    'Design', // Specialization
    'The Cooper Union for the Advancement of Science and Art', // Univer
    '0', // Current
    '1978-01-01', // Start Time
    '1982-01-01', // End Time
    1
);

// Add languages
$db->addLanguages($i,array(20));





// Insert Users
$i = 1002;
$db->insertCoolUser(
    $i, //id
    'Alice', // Name
    'Cho', // Surname
    'alicecho@gmail.com', // Email
    '140', // Industry
    '3', // Goal
    '4b6dd44af964a520c0932ce3', // Foursquare
    'Under Wonder', // Place
    null, // Summary
    $i.'.png', // Photo
    '380937864369', // Phone
    '50.4393928530707', // Lat
    '30.517637729644775', // Lng
    'alicecho', // Skype
    '4', // Rating
    '12', // Experience
    'CjQwAAAA2-HDsdQ_XpT6uDCjb6ofXs2RvBhIvQ--qMsnADlBFqWfSAOsCmUG_rmUG-ygHe69EhAFFCvKUfy-aXMTj8xiDtkxGhRKt2mmPZZUXYzqieEyn1l4eA7DoA', // City
    'Kiev, UA' // City name
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Art director', // Position name
    'Wired Magazine', // Company
    '1', // Current
    '2010-08-01', // Start Time
    'null', // End Time
    0
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Art director', // Position name
    'Print Magazine', // Company
    '0', // Current
    '2009-03-01', // Start Time
    '2010-06-01', // End Time
    0
);


// Add Education
$db->addJob(
    $i, // ID
    'Visual Communication', // Specialization
    'Alberta College of Art and Design', // Univer
    '0', // Current
    '1996-01-01', // Start Time
    '2001-01-01', // End Time
    1
);

// Add languages
$db->addLanguages($i,array(20));



// Insert Users
$i = 1003;
$db->insertCoolUser(
    $i, //id
    'Stephen', // Name
    'Walker', // Surname
    'walker@gmail.com', // Email
    '82', // Industry
    '3', // Goal
    '4c3b2a2d0cabef3b0786b94b', // Foursquare
    'National Opera of Ukraine', // Place
    'Resourceful accomplished creative professional with 13 years of experience, delivering market leading content for brands within budgetary and time constraints without sacrificing integrity of the product. ', // Summary
    $i.'.png', // Photo
    '380449434356', // Phone
    '50.4466422511271', // Lat
    '30.512402057647705', // Lng
    'walker', // Skype
    '5', // Rating
    '7', // Experience
    'CjQwAAAA2-HDsdQ_XpT6uDCjb6ofXs2RvBhIvQ--qMsnADlBFqWfSAOsCmUG_rmUG-ygHe69EhAFFCvKUfy-aXMTj8xiDtkxGhRKt2mmPZZUXYzqieEyn1l4eA7DoA', // City
    'Kiev, UA' // City name
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Art Buyer', // Position name
    'Sephora', // Company
    '1', // Current
    '2012-09-01', // Start Time
    'null', // End Time
    0
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Photo Director', // Position name
    'Nylon Magazine', // Company
    '0', // Current
    '2007-02-01', // Start Time
    '2012-09-01', // End Time
    0
);


// Add Education
$db->addJob(
    $i, // ID
    'Visual Communication', // Specialization
    'Goldsmiths College', // Univer
    '0', // Current
    '2002-01-01', // Start Time
    '2007-01-01', // End Time
    1
);

// Add languages
$db->addLanguages($i,array(20));




// Insert Users
$i = 1004;
$db->insertCoolUser(
    $i, //id
    'Mark', // Name
    'Brandon', // Surname
    'markbrandon@gmail.com', // Email
    '4', // Industry
    '3', // Goal
    '4be6d8bfbcef2d7f388005e5', // Foursquare
    'Capo di Monte', // Place
    'CEO/Co-Founder of StackSearch, a venture-backed enterprise software company based in Fayetteville, AR.', // Summary
    $i.'.png', // Photo
    '38044468392', // Phone
    '50.43575065347702', // Lat
    '30.51277756690979', // Lng
    'markbrandon', // Skype
    '5', // Rating
    '7', // Experience
    'CjQwAAAA2-HDsdQ_XpT6uDCjb6ofXs2RvBhIvQ--qMsnADlBFqWfSAOsCmUG_rmUG-ygHe69EhAFFCvKUfy-aXMTj8xiDtkxGhRKt2mmPZZUXYzqieEyn1l4eA7DoA', // City
    'Kiev, UA' // City name
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Co-Founder', // Position name
    'StackSearch', // Company
    '1', // Current
    '2010-02-01', // Start Time
    'null', // End Time
    0
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Fusion Middleware Sales Representative', // Position name
    'Oracle Corp', // Company
    '0', // Current
    '2008-10-01', // Start Time
    '2010-01-01', // End Time
    0
);


// Add Education
$db->addJob(
    $i, // ID
    'History', // Specialization
    'University of Texas at Austin', // Univer
    '0', // Current
    '1988-01-01', // Start Time
    '1992-01-01', // End Time
    1
);

// Add languages
$db->addLanguages($i,array(20));




// Insert Users
$i = 1005;
$db->insertCoolUser(
    $i, //id
    'Sam', // Name
    'Friday', // Surname
    'samfriday@gmail.com', // Email
    '99', // Industry
    '3', // Goal
    '4bb8f38fcf2fc9b67710a002', // Foursquare
    'Art Club 44', // Place
    'Specialties: ArchiCAD 6.5, 7, 8, 9, 10, 11, 12, 13 & 14 SketchUP Pro 6, Macromedia Flash Professional 8, Adobe Photoshop CS, HTML, CSS, WordPress and Web Design', // Summary
    $i.'.png', // Photo
    '380448430993', // Phone
    '50.44389567300544', // Lat
    '30.52001953125', // Lng
    'samfriday', // Skype
    '4', // Rating
    '6', // Experience
    'CjQwAAAA2-HDsdQ_XpT6uDCjb6ofXs2RvBhIvQ--qMsnADlBFqWfSAOsCmUG_rmUG-ygHe69EhAFFCvKUfy-aXMTj8xiDtkxGhRKt2mmPZZUXYzqieEyn1l4eA7DoA', // City
    'Kiev, UA' // City name
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Designer', // Position name
    'StackSearch', // Company
    '1', // Current
    '2013-03-01', // Start Time
    'null', // End Time
    0
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Private Contractor', // Position name
    'Friday Design', // Company
    '0', // Current
    '2008-06-01', // Start Time
    '2013-01-01', // End Time
    0
);


// Add Education
$db->addJob(
    $i, // ID
    'Architecture', // Specialization
    'University of Arkansas at Fayetteville', // Univer
    '0', // Current
    '1994-01-01', // Start Time
    '2000-01-01', // End Time
    1
);

// Add languages
$db->addLanguages($i,array(20));




// Insert Users
$i = 1006;
$db->insertCoolUser(
    $i, //id
    'Svetlana', // Name
    'Avellan', // Surname
    'avellan@gmail.com', // Email
    '32', // Industry
    '3', // Goal
    '4bdddbd20ee3a593c9ef2eb0', // Foursquare
    'Coffee House', // Place
    'I recently graduated with an Entertainment Management degree from Missouri State University. I am an individual who is always on the go. Not a day goes without me feeling productive when I lay to rest. I am passionate about everything I do. I love those who have become close to me throughout my college career both on a personal and professional level. I have experience in effective and efficient leadership. I love planning events and managing them. I believe in diversity and strive to appeal to everyone and their views and values. I am a nerd for education. I love learning something new, especially when I can apply that knowledge to my life. I try to smile as often as I can because it can be contagious and not a day should this life go without happiness.', // Summary
    $i.'.png', // Photo
    '380447543255', // Phone
    '50.450744450679416', // Lat
    '30.52599522090145', // Lng
    'avellan', // Skype
    '4', // Rating
    '3', // Experience
    'CjQwAAAA2-HDsdQ_XpT6uDCjb6ofXs2RvBhIvQ--qMsnADlBFqWfSAOsCmUG_rmUG-ygHe69EhAFFCvKUfy-aXMTj8xiDtkxGhRKt2mmPZZUXYzqieEyn1l4eA7DoA', // City
    'Kiev, UA' // City name
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Concessions Supervisor', // Position name
    'Delaware North Companies', // Company
    '1', // Current
    '2013-06-01', // Start Time
    'null', // End Time
    0
);

// Add Jobs
$db->addJob(
    $i, // ID
    'House Manager', // Position name
    'Hall for the Performing Arts', // Company
    '0', // Current
    '2011-10-01', // Start Time
    '2013-06-01', // End Time
    0
);


// Add Education
$db->addJob(
    $i, // ID
    'Entertainment Management', // Specialization
    'Missouri State University', // Univer
    '0', // Current
    '2010-01-01', // Start Time
    '2013-01-01', // End Time
    1
);

// Add languages
$db->addLanguages($i,array(20));




// Insert Users
$i = 1007;
$db->insertCoolUser(
    $i, //id
    'Michael', // Name
    'Paladino', // Surname
    'paladino@gmail.com', // Email
    '4', // Industry
    '3', // Goal
    '4c57c4f17329c9280baa8f80', // Foursquare
    'Coffee House', // Place
    null, // Summary
    $i.'.png', // Photo
    '380447575455', // Phone
    '50.44740296870534', // Lat
    '30.488598403642733', // Lng
    'avellan', // Skype
    '5', // Rating
    '8', // Experience
    'CjQwAAAA2-HDsdQ_XpT6uDCjb6ofXs2RvBhIvQ--qMsnADlBFqWfSAOsCmUG_rmUG-ygHe69EhAFFCvKUfy-aXMTj8xiDtkxGhRKt2mmPZZUXYzqieEyn1l4eA7DoA', // City
    'Kiev, UA' // City name
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Co-founder & CTO', // Position name
    'RevUnit', // Company
    '1', // Current
    '2012-09-01', // Start Time
    'null', // End Time
    0
);

// Add Jobs
$db->addJob(
    $i, // ID
    'CTO', // Position name
    'Overwatch', // Company
    '0', // Current
    '2005-03-01', // Start Time
    '2013-09-01', // End Time
    0
);


// Add Education
$db->addJob(
    $i, // ID
    'Computer Information Systems', // Specialization
    'University of Arkansas at Fayetteville', // Univer
    '0', // Current
    '1997-01-01', // Start Time
    '2001-01-01', // End Time
    1
);

// Add languages
$db->addLanguages($i,array(20));




// Insert Users
$i = 1008;
$db->insertCoolUser(
    $i, //id
    'Matt', // Name
    'Dromi', // Surname
    'dromi@gmail.com', // Email
    '80', // Industry
    '3', // Goal
    '4eb44adc93ad23656fab2aad', // Foursquare
    'Friends', // Place
    'I help business leaders initiate, navigate, and sustain change.

Currently, I focus in these key areas: transparency for social media, technology for process optimization, and interplanetary politics for fun. 

If it can be done better, smarter, or with more flair (preferably all three), find me. My team is amazing, and we can help you.', // Summary
    $i.'.png', // Photo
    '380507223131', // Phone
    '50.4569829730166', // Lat
    '30.434411714911324', // Lng
    'dromi', // Skype
    '4', // Rating
    '11', // Experience
    'CjQwAAAA2-HDsdQ_XpT6uDCjb6ofXs2RvBhIvQ--qMsnADlBFqWfSAOsCmUG_rmUG-ygHe69EhAFFCvKUfy-aXMTj8xiDtkxGhRKt2mmPZZUXYzqieEyn1l4eA7DoA', // City
    'Kiev, UA' // City name
);

// Add Jobs
$db->addJob(
    $i, // ID
    'CxO & Satisfaction Guarantor', // Position name
    'modthink', // Company
    '1', // Current
    '2011-04-01', // Start Time
    'null', // End Time
    0
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Technical Strategy Architect', // Position name
    'Rockfish/WPP', // Company
    '0', // Current
    '2005-03-01', // Start Time
    '2011-04-01', // End Time
    0
);


// Add Education
$db->addJob(
    $i, // ID
    'Digital Media', // Specialization
    'John Brown University', // Univer
    '0', // Current
    '2002-01-01', // Start Time
    '2004-01-01', // End Time
    1
);

// Add languages
$db->addLanguages($i,array(20));





// Insert Users
$i = 1009;
$db->insertCoolUser(
    $i, //id
    'Hannah', // Name
    'McCulloch', // Surname
    'mcculloch@gmail.com', // Email
    '80', // Industry
    '3', // Goal
    '4db17ee7316a8fa689930ea1', // Foursquare
    'Aroma espresso bar', // Place
    null, // Summary
    $i.'.png', // Photo
    '380703468989', // Phone
    '50.43780070648702', // Lat
    '30.52877426147461', // Lng
    'hahaha', // Skype
    '5', // Rating
    '5', // Experience
    'CjQwAAAA2-HDsdQ_XpT6uDCjb6ofXs2RvBhIvQ--qMsnADlBFqWfSAOsCmUG_rmUG-ygHe69EhAFFCvKUfy-aXMTj8xiDtkxGhRKt2mmPZZUXYzqieEyn1l4eA7DoA', // City
    'Kiev, UA' // City name
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Social strategist', // Position name
    'modthink', // Company
    '1', // Current
    '2013-03-01', // Start Time
    'null', // End Time
    0
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Sales associate', // Position name
    'Pack Rat Outdoor Center', // Company
    '0', // Current
    '2009-03-01', // Start Time
    '2013-03-01', // End Time
    0
);


// Add Education
$db->addJob(
    $i, // ID
    'Journalism', // Specialization
    'University of Arkansas', // Univer
    '0', // Current
    '2010-01-01', // Start Time
    '2014-01-01', // End Time
    1
);

// Add languages
$db->addLanguages($i,array(20));




// Insert Users
$i = 1010;
$db->insertCoolUser(
    $i, //id
    'Cade', // Name
    'Collister', // Surname
    'collister@gmail.com', // Email
    '113', // Industry
    '3', // Goal
    '5194d09fabd8e46d111f91d2', // Foursquare
    'Babel', // Place
    'Cade has worked in the tech industry for over 14 years and specializes in web design/development, UX/UI, and mobile design/development. Cade has worked with household names, such as Dell, Microsoft, Continental Tire, ABN Amro, and CompUSA, providing web design, visual design, and user experience expertise. Cade also has proven expertise in the mobile arena, having worked as lead developer on proprietary medical software for Handheldmed (partnered with Merck and Lippincott) and mobile alert solutions for Alltel Communications.

Cade recently left Southern Bancorp, a $2.1B development bank, as Creative Director, where he worked to re-brand the organization, revolutionize their web presence and customer experience, and institute strict marketing and SEO practices.

Specialties:web design, user experience, visual design, interactive media, mobile interface design and development, branding and marketing, SEO, SMO, WordPress, user interface, design, brands', // Summary
    $i.'.png', // Photo
    '380639456388', // Phone
    '50.43577798810115', // Lat
    '30.51329791545868', // Lng
    'cadeco', // Skype
    '4', // Rating
    '16', // Experience
    'CjQwAAAA2-HDsdQ_XpT6uDCjb6ofXs2RvBhIvQ--qMsnADlBFqWfSAOsCmUG_rmUG-ygHe69EhAFFCvKUfy-aXMTj8xiDtkxGhRKt2mmPZZUXYzqieEyn1l4eA7DoA', // City
    'Kiev, UA' // City name
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Creative Director', // Position name
    'Acumen Brands', // Company
    '1', // Current
    '2011-09-01', // Start Time
    'null', // End Time
    0
);

// Add Jobs
$db->addJob(
    $i, // ID
    'Creative Partner', // Position name
    'ISOS Group', // Company
    '0', // Current
    '2009-03-01', // Start Time
    'null', // End Time
    0
);


// Add Education
$db->addJob(
    $i, // ID
    'Graphic Design', // Specialization
    'University of Arkansas', // Univer
    '0', // Current
    '1995-01-01', // Start Time
    '2000-01-01', // End Time
    1
);

// Add languages
$db->addLanguages($i,array(20));
