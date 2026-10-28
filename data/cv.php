<?php
// Single source for the site's content. build.php turns this into the page,
// structured data, llms.txt and the sitemap. Keep it in step with the CV.
//
// {years} in any text is replaced with the years since career_start.

return [
	'name' => 'Martin Wadeson',
	'alternate_name' => 'Jarf',
	'job_title' => 'Lead Full Stack Developer',
	'headline' => 'Lead Full Stack Developer | PHP | APIs | Databases',
	'url' => 'https://martinwadeson.com/',
	'email' => 'martin.wadeson@gmail.com',
	'locality' => 'Warrington',
	'country' => 'GB',
	'location' => 'Warrington, UK',
	'career_start' => '2012-09-01',

	'description' => 'Lead full stack developer with {years} years\' commercial experience in PHP, REST APIs, third-party integrations and high-performance databases.',

	'profile' => [
		'Lead developer with {years} years\' commercial experience seeing projects through from first conversation to delivery. I work directly with clients and senior management to understand what they need, then plan the architecture, set timelines, agree scope and lead the build.',
		'Most of my work has been in PHP, building REST APIs, bespoke systems, third-party integrations and large, high-performance databases. At Moneta I developed the odds platform for BettingOdds.com, which handled tens of thousands of requests a minute, and moved hundreds of sites onto one codebase so urgent fixes went out in under an hour instead of a day.',
		'Outside work I run comparison.love, a live household-costs site built with Claude Code that keeps 34 official data sources up to date and runs nearly 400 automated tests before every deploy.',
	],

	'links' => [
		['label' => 'GitHub', 'url' => 'https://github.com/Jarf', 'icon' => 'github.svg'],
		['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/martin-wadeson-a936a922/', 'icon' => 'linkedin.svg'],
	],

	'skills' => [
		['group' => 'Back end', 'items' => ['PHP ({years} years, OOP)', 'Laravel', 'CodeIgniter', 'Drupal', 'Twig', 'Node.js', 'Python', 'Composer', 'Magento']],
		['group' => 'Front end', 'items' => ['JavaScript', 'TypeScript', 'Vue', 'React', 'Next.js', 'Tailwind CSS', 'SCSS']],
		['group' => 'Data', 'items' => ['MySQL', 'MariaDB', 'Microsoft SQL Server', 'MongoDB', 'Redis'], 'note' => 'Including schema design, indexing and query tuning for large and distributed datasets.'],
		['group' => 'APIs and integration', 'items' => ['RESTful API design', 'Third-party and partner integrations', 'Real-time data feeds', 'Stripe payments and webhooks']],
		['group' => 'Security', 'items' => ['OWASP secure coding', 'OAuth 2.0', 'JWT authentication', 'Server hardening', 'Firewalls']],
		['group' => 'Infrastructure', 'items' => ['Linux and Windows servers', 'Apache', 'Docker', 'Azure', 'AWS', 'OVHcloud', 'Linode', 'Virtualmin/Webmin', 'Git', 'SVN', 'Jenkins', 'GitHub Actions']],
		['group' => 'Testing and performance', 'items' => ['PHPUnit', 'Pest', 'Playwright', 'PHPStan', 'Xdebug', 'KCacheGrind', 'Lighthouse']],
		['group' => 'SEO', 'items' => ['Technical SEO', 'Screaming Frog', 'Google Search Console', 'Google Analytics', 'Structured data', 'llms.txt']],
		['group' => 'Documentation', 'note' => 'I\'ve written technical documentation for all my professional work over the last nine years, for internal development teams and for third parties.'],
		['group' => 'Working with people', 'note' => 'Running client meetings, explaining technical options to non-technical people, estimates, scope negotiation, interviewing, Agile/Scrum (Jira, Trello).'],
	],

	// Newest first. 'end' => null means current role.
	'experience' => [
		[
			'role' => 'Lead Developer',
			'company' => 'CCSW Ltd',
			'start' => '2022-01',
			'end' => null,
			'points' => [
				'As lead developer I work directly with the CTO and our clients, and I make the architecture and technology decisions on client projects.',
				'Built MoneySuperMarket\'s boiler comparison system. Customers fill in one standard questionnaire, it goes out to several providers\' APIs, and their results come back in a common format for the comparison page. I wrote the API documentation the boiler providers worked from.',
				'Integrated MGY\'s website with the Streets property platform, covering 4,000–6,000 properties at a time (for sale, sold and everything in between). I set up webhooks so new listings and changes made in Streets went live on their website straight away.',
				'Built APIs linking St John Ambulance Wales\'s in-house systems with Office 365, Rosterfy and GoCardless for 3,000–4,000 active users. This helped them move over to Rosterfy without losing records, and made sure emails were generated and product licences assigned correctly.',
				'When the agency building the St John Ambulance Cymru website blamed our API for slow page loads, I profiled and inspected it myself and found the problem was a huge image loading before the page could render, much to the relief of our CTO.',
				'Run client meetings where we work out requirements, set timelines and agree scope changes, and I explain the technical options in plain English so the client can choose.',
				'Manage and secure our Linux and Windows servers on OVHcloud, and script repetitive deployment and maintenance jobs.',
				'Interview and assess candidates for developer roles.',
			],
		],
		[
			'role' => 'Senior Developer',
			'company' => 'Moneta Communications',
			'start' => '2016-09',
			'end' => '2021-12',
			'points' => [
				'Moved hundreds of separate websites onto one shared codebase deployed with Git and Jenkins. Until then every site was updated by hand over FTP (an urgent fix meant a full day of frantic work); afterwards the fix was made once and was live in under an hour.',
				'Developed the odds platform for BettingOdds.com, which handled tens of thousands of requests and odds updates a minute. Live events such as ongoing horse races were given queue priority so their odds updated as quickly as possible.',
				'Integrated partner APIs into the company\'s main product.',
				'Set up and ran the company\'s Linux and Windows servers on Microsoft Azure.',
				'Gave product managers development estimates and advice on architecture.',
				'Interviewed and assessed candidates for developer roles.',
			],
		],
		[
			'role' => 'Application Developer',
			'company' => 'Localstars',
			'start' => '2014-12',
			'end' => '2016-09',
			'points' => [
				'Extended and updated existing PHP systems as clients\' needs changed.',
				'Built adapters and APIs so client systems could talk to our in-house platforms.',
				'Worked with clients directly to agree what they needed, then built it.',
			],
		],
		[
			'role' => 'Web Developer',
			'company' => 'Hallnet Ltd',
			'start' => '2012-09',
			'end' => '2014-12',
			'points' => [
				'Built websites and web systems for clients including CV-Library, sometimes from detailed specs and sometimes from rough briefs, working alone or in teams of 3–5.',
				'Handled client support directly and talked clients through the pros and cons of different approaches.',
			],
		],
	],

	// Stage select. 'image' is either an SVG logo or a screenshot base name
	// with 360w/600w WebP versions in public/img/.
	'projects' => [
		[
			'title' => 'BettingOdds.com',
			'url' => 'https://www.bettingodds.com/',
			'tag' => 'Moneta',
			'logo' => ['file' => 'bet.svg', 'width' => 468, 'height' => 70, 'dark' => true], // white logo, needs a dark backdrop
			'text' => 'The odds platform, handling tens of thousands of requests and odds updates a minute, with live events given queue priority.',
		],
		[
			'title' => 'MoneySuperMarket boiler comparison',
			'url' => 'https://www.moneysupermarket.com/new-boiler/',
			'tag' => 'CCSW',
			'logo' => ['file' => 'msm.svg', 'width' => 468, 'height' => 187],
			'text' => 'One questionnaire sent to several providers\' APIs, with their results returned in a common format for the comparison page.',
		],
		[
			'title' => 'St John Ambulance Cymru',
			'url' => 'https://www.sjacymru.org.uk/',
			'tag' => 'CCSW',
			'logo' => ['file' => 'stj.svg', 'width' => 468, 'height' => 176],
			'text' => 'APIs linking in-house systems with Office 365, Rosterfy and GoCardless for 3,000–4,000 active users.',
		],
		[
			'title' => 'comparison.love',
			'url' => 'https://comparison.love/',
			'tag' => 'Personal',
			'screenshot' => 'comparisonlove',
			'text' => 'A live UK website with 13 free tools for comparing household costs. Laravel, MySQL and 27 scheduled imports keeping 34 official sources up to date.',
		],
		[
			'title' => 'CDDA Story Browser',
			'url' => 'https://cddastory.jarfjam.co.uk/',
			'tag' => 'Personal',
			'screenshot' => 'cddastory',
			'text' => 'Parses the files of the open source game Cataclysm: Dark Days Ahead to collect its snippets of lore for easy reading.',
		],
		[
			'title' => 'ArenaDekt',
			'url' => 'https://arenadekt.jarfjam.co.uk/',
			'tag' => 'Personal',
			'screenshot' => 'arenadekt',
			'text' => 'Converts Magic: The Gathering paper decks from Archidekt to the digital historic brawl format using the Scryfall API.',
		],
	],

	'qualifications' => [
		['title' => 'BSc Computing (Software Development)', 'place' => 'University of Central Lancashire', 'years' => '2011–2012'],
		['title' => 'HND Software Development', 'place' => 'Wigan & Leigh College', 'years' => '2008–2010'],
	],
];
