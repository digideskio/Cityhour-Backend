<?php 

$data = '{
	"data":
	[

		{
			"id": 5563,
			"user_id": 1165,
			"name": "Certified Computer Technician",
			"company": "Lincoln Unified School District",
			"current": 1,
			"start_time": "2008-03-01",
			"end_time": "2014-04-01",
			"type": 0,
			"active": 1
		},
		{
			"id": 5564,
			"user_id": 1165,
			"name": "Computer Technician I",
			"company": "Modesto City Schools",
			"current": 0,
			"start_time": "2002-02-01",
			"end_time": "2008-03-01",
			"type": 0,
			"active": 0
		},
		{
			"id": 5565,
			"user_id": 1165,
			"name": "Web Application Developer - Temporary",
			"company": "Info Con, Inc.",
			"current": 0,
			"start_time": "2002-01-01",
			"end_time": "2002-02-01",
			"type": 0,
			"active": 0
		},
		{
			"id": 5566,
			"user_id": 1165,
			"name": "System Administrator - Temporary",
			"company": "Byron Union School District",
			"current": 0,
			"start_time": "2001-10-01",
			"end_time": "2002-01-01",
			"type": 0,
			"active": 0
		},
		{
			"id": 5567,
			"user_id": 1165,
			"name": "System Administrator",
			"company": "El Concilio Para Los Hispano-Hablantes",
			"current": 0,
			"start_time": "2000-05-01",
			"end_time": "2001-06-01",
			"type": 0,
			"active": 0
		},
		{
			"id": 5568,
			"user_id": 1165,
			"name": "Full-Time Missionary - Unpaid",
			"company": "The Church of Jesus Christ of Latter-day Saints",
			"current": 0,
			"start_time": "1998-04-01",
			"end_time": "2000-03-01",
			"type": 0,
			"active": 0
		},
		{
			"id": 5569,
			"user_id": 1165,
			"name": "Software Developer and Website Administrator - Paid Internship",
			"company": "Flagship Corporation",
			"current": 0,
			"start_time": "1997-09-01",
			"end_time": "1998-03-01",
			"type": 0,
			"active": 0
		}
	]
}';

include_once('Carbon.php');
use Carbon\Carbon;

$data = json_decode($data,true);

$g = 0;
foreach ($data['data'] as $row) {
	$a = Carbon::createFromTimeStamp(strtotime($row['start_time']));
	$b = Carbon::createFromTimeStamp(strtotime($row['end_time']));
	$z = $a->diffInMonths($b);
	var_dump($z);
	$g = $g + $z;
}

var_dump($g);
var_dump($g/12);
