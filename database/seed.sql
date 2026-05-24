-- Switches Lib — seed data
-- Run after schema.sql. Safe to re-run: each section clears its table first.
--
-- Note on data: specs below reflect commonly published official figures.
-- Where an official value is genuinely unclear, numeric fields are left NULL
-- (rendered as "Unknown") and text fields use the literal 'Unknown'.

USE switches_lib;

-- ---------------------------------------------------------------------------
-- tags  (fixed enums — PRD §14)
-- ---------------------------------------------------------------------------
DELETE FROM tags;

INSERT INTO tags (type, name, description) VALUES
-- Switch Type
('switch_type', 'Linear',         'Smooth keypress with no bump or click.'),
('switch_type', 'Tactile',        'Noticeable bump on actuation.'),
('switch_type', 'Clicky',         'Tactile bump accompanied by an audible click.'),
('switch_type', 'Silent Linear',  'Linear feel with built-in sound dampening.'),
('switch_type', 'Silent Tactile', 'Tactile feel with built-in sound dampening.'),
-- Sound Profile
('sound_profile', 'Creamy',  'Smooth, soft, and blended sound.'),
('sound_profile', 'Clacky',  'Crisp, sharp, and higher-pitched sound.'),
('sound_profile', 'Thocky',  'Lower, deeper, and fuller sound.'),
('sound_profile', 'Muted',   'Dampened or quieter sound.'),
('sound_profile', 'Poppy',   'Lively and bouncy sound character.'),
('sound_profile', 'Bright',  'Clear and high-frequency sound character.'),
('sound_profile', 'Unknown', 'Official description does not provide a clear sound profile.'),
-- Feel Profile
('feel_profile', 'Smooth',  'Official description emphasizes smoothness.'),
('feel_profile', 'Light',   'Bottom-out force is 45g or below.'),
('feel_profile', 'Medium',  'Bottom-out force is between 46g and 60g.'),
('feel_profile', 'Heavy',   'Bottom-out force is above 60g.'),
('feel_profile', 'Tactile', 'Switch has tactile feedback.'),
('feel_profile', 'Snappy',  'Fast or crisp return feel.'),
('feel_profile', 'Stable',  'Official description emphasizes low wobble or stability.'),
('feel_profile', 'Unknown', 'Official description does not provide a clear feel profile.'),
-- Recommended Use
('recommended_use', 'Beginner Friendly', 'A good starting point for newcomers.'),
('recommended_use', 'Office',            'Suited to shared or quiet work environments.'),
('recommended_use', 'Gaming',            'Suited to fast-paced gaming.'),
('recommended_use', 'Typing',            'Suited to long typing sessions.'),
('recommended_use', 'Quiet Setup',       'Suited to noise-sensitive setups.'),
('recommended_use', 'Budget Build',      'Affordable option for budget builds.'),
('recommended_use', 'Unknown',           'No clear recommended use.');

-- ---------------------------------------------------------------------------
-- designers
-- ---------------------------------------------------------------------------
DELETE FROM designers;

INSERT INTO designers (name, website, country) VALUES
('Gateron',   'https://www.gateron.com',   'China'),
('Cherry',    'https://www.cherry.de',     'Germany'),
('Akko',      'https://en.akkogear.com',   'China'),
('NovelKeys', 'https://novelkeys.com',     'USA'),
('Tecsee',    NULL,                        'China'),
('Gazzew',    NULL,                        'Unknown'),
('Durock',    NULL,                        'China'),
('Kailh',     'https://www.kailh.com',     'China');

-- ---------------------------------------------------------------------------
-- switches  (15 — Linear x6, Tactile x4, Clicky x2, Silent Linear x2, Silent Tactile x1)
-- ---------------------------------------------------------------------------
DELETE FROM switches;

INSERT INTO switches
(slug, name, designer_id, series, variant, manufacturer, switch_category, switch_type, description, release_date,
 initial_force, actuation_force, bottom_out_force, tactile_force, actuation_travel, total_travel, spring_length, spring_type,
 top_housing_material, bottom_housing_material, stem_material, stem_type, contact_material, pin_count,
 led_diffuser, rgb_support, factory_lubed, is_silent, silent_structure,
 sound_profile, feel_profile, recommended_use, image_url, status)
VALUES
-- Linear
('gateron-milky-yellow-pro', 'Gateron Milky Yellow Pro', (SELECT id FROM designers WHERE name='Gateron'),
 'Yellow', 'Milky Pro', 'Gateron', 'Mechanical MX', 'Linear',
 'A smooth, affordable linear switch and a popular beginner pick.', '2021-01-01',
 15.0, 50.0, 67.0, NULL, 2.00, 4.00, NULL, 'Unknown',
 'Polycarbonate', 'Nylon', 'POM', 'Unknown', 'Unknown', 5,
 'No', 'No', 'Yes', 'No', NULL,
 'Creamy', 'Smooth', 'Budget Build', NULL, 'approved'),

('gateron-oil-king', 'Gateron Oil King', (SELECT id FROM designers WHERE name='Gateron'),
 NULL, NULL, 'Gateron', 'Mechanical MX', 'Linear',
 'A deep, thocky factory-lubed linear switch.', '2022-01-01',
 15.0, 37.0, 55.0, NULL, 2.00, 3.50, NULL, 'Unknown',
 'Nylon', 'Nylon', 'INK', 'Unknown', 'Unknown', 5,
 'No', 'No', 'Yes', 'No', NULL,
 'Thocky', 'Smooth', 'Typing', NULL, 'approved'),

('cherry-mx-red', 'Cherry MX Red', (SELECT id FROM designers WHERE name='Cherry'),
 NULL, NULL, 'Cherry', 'Mechanical MX', 'Linear',
 'The classic light linear switch, popular for gaming.', '2008-01-01',
 NULL, 45.0, NULL, NULL, 2.00, 4.00, NULL, 'Unknown',
 'Nylon', 'Nylon', 'POM', 'Unknown', 'Gold crosspoint', 3,
 'No', 'No', 'No', 'No', NULL,
 'Bright', 'Light', 'Gaming', NULL, 'approved'),

('akko-cs-jelly-black', 'Akko CS Jelly Black', (SELECT id FROM designers WHERE name='Akko'),
 'CS', 'Jelly Black', 'Akko', 'Mechanical MX', 'Linear',
 'A deep-sounding linear switch with a translucent housing.', '2021-01-01',
 NULL, 50.0, 60.0, NULL, 1.90, 4.00, NULL, 'Unknown',
 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 5,
 'Unknown', 'Yes', 'No', 'No', NULL,
 'Thocky', 'Medium', 'Beginner Friendly', NULL, 'approved'),

('novelkeys-cream', 'NovelKeys Cream', (SELECT id FROM designers WHERE name='NovelKeys'),
 NULL, NULL, 'Kailh', 'Mechanical MX', 'Linear',
 'An all-POM linear switch known for its distinctive sound that breaks in over time.', '2019-01-01',
 NULL, 35.0, 55.0, NULL, 2.00, 4.00, NULL, 'Unknown',
 'POM', 'POM', 'POM', 'Unknown', 'Unknown', 5,
 'No', 'No', 'No', 'No', NULL,
 'Clacky', 'Smooth', 'Typing', NULL, 'approved'),

('tecsee-carrot', 'Tecsee Carrot', (SELECT id FROM designers WHERE name='Tecsee'),
 NULL, NULL, 'Tecsee', 'Mechanical MX', 'Linear',
 'A creamy medium-weight linear switch.', '2022-01-01',
 NULL, 45.0, 62.0, NULL, 2.00, 4.00, NULL, 'Unknown',
 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 5,
 'Unknown', 'Unknown', 'No', 'No', NULL,
 'Creamy', 'Medium', 'Beginner Friendly', NULL, 'approved'),

-- Tactile
('cherry-mx-brown', 'Cherry MX Brown', (SELECT id FROM designers WHERE name='Cherry'),
 NULL, NULL, 'Cherry', 'Mechanical MX', 'Tactile',
 'The classic light tactile switch, an all-round office choice.', '2008-01-01',
 NULL, 45.0, NULL, 55.0, 2.00, 4.00, NULL, 'Unknown',
 'Nylon', 'Nylon', 'POM', 'Unknown', 'Gold crosspoint', 3,
 'No', 'No', 'No', 'No', NULL,
 'Muted', 'Tactile', 'Office', NULL, 'approved'),

('gazzew-boba-u4t', 'Gazzew Boba U4T', (SELECT id FROM designers WHERE name='Gazzew'),
 'Boba', 'U4T', 'Outemu', 'Mechanical MX', 'Tactile',
 'A sharp, poppy tactile switch designed by Gazzew and made by Outemu.', '2020-01-01',
 NULL, NULL, 62.0, NULL, 2.00, 3.50, NULL, 'Unknown',
 'Proprietary blend', 'Proprietary blend', 'Unknown', 'Unknown', 'Unknown', 5,
 'No', 'No', 'No', 'No', NULL,
 'Poppy', 'Snappy', 'Typing', NULL, 'approved'),

('durock-t1', 'Durock T1', (SELECT id FROM designers WHERE name='Durock'),
 NULL, 'T1', 'Durock', 'Mechanical MX', 'Tactile',
 'A pronounced, clacky tactile switch.', '2020-01-01',
 NULL, NULL, 67.0, 65.0, 2.00, 4.00, NULL, 'Unknown',
 'Polycarbonate', 'Nylon', 'POM', 'Unknown', 'Unknown', 5,
 'No', 'Yes', 'No', 'No', NULL,
 'Clacky', 'Tactile', 'Typing', NULL, 'approved'),

('akko-v3-cream-blue-pro', 'Akko V3 Cream Blue Pro', (SELECT id FROM designers WHERE name='Akko'),
 'V3', 'Cream Blue Pro', 'Akko', 'Mechanical MX', 'Tactile',
 'A beginner-friendly tactile switch with a poppy sound.', '2022-01-01',
 NULL, NULL, 55.0, NULL, 2.00, 3.50, NULL, 'Unknown',
 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 5,
 'Unknown', 'Unknown', 'Yes', 'No', NULL,
 'Poppy', 'Tactile', 'Beginner Friendly', NULL, 'approved'),

-- Clicky
('cherry-mx-blue', 'Cherry MX Blue', (SELECT id FROM designers WHERE name='Cherry'),
 NULL, NULL, 'Cherry', 'Mechanical MX', 'Clicky',
 'The classic clicky switch with a distinct click and bump.', '2008-01-01',
 NULL, 50.0, NULL, 60.0, 2.20, 4.00, NULL, 'Unknown',
 'Nylon', 'Nylon', 'POM', 'Unknown', 'Gold crosspoint', 3,
 'No', 'No', 'No', 'No', NULL,
 'Clacky', 'Tactile', 'Typing', NULL, 'approved'),

('kailh-box-white', 'Kailh BOX White', (SELECT id FROM designers WHERE name='Kailh'),
 'BOX', 'White', 'Kailh', 'Mechanical MX', 'Clicky',
 'A crisp clicky switch using a click-bar mechanism, with a dustproof BOX stem.', '2017-01-01',
 NULL, 50.0, NULL, NULL, 1.80, 3.60, NULL, 'Unknown',
 'Unknown', 'Unknown', 'Unknown', 'BOX', 'Unknown', 5,
 'Unknown', 'Yes', 'No', 'No', NULL,
 'Bright', 'Snappy', 'Typing', NULL, 'approved'),

-- Silent Linear
('gateron-silent-yellow', 'Gateron Silent Yellow', (SELECT id FROM designers WHERE name='Gateron'),
 'Yellow', 'Silent', 'Gateron', 'Mechanical MX', 'Silent Linear',
 'A quiet take on the popular Gateron Yellow linear switch.', '2021-01-01',
 NULL, 50.0, 67.0, NULL, 2.00, 4.00, NULL, 'Unknown',
 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 5,
 'No', 'No', 'No', 'Yes', 'Dampened stem',
 'Muted', 'Smooth', 'Quiet Setup', NULL, 'approved'),

('cherry-mx-silent-red', 'Cherry MX Silent Red', (SELECT id FROM designers WHERE name='Cherry'),
 NULL, 'Silent', 'Cherry', 'Mechanical MX', 'Silent Linear',
 'A light linear switch with integrated rubber dampeners for quiet operation.', '2016-01-01',
 NULL, 45.0, NULL, NULL, 1.90, 3.70, NULL, 'Unknown',
 'Nylon', 'Nylon', 'POM', 'Unknown', 'Gold crosspoint', 3,
 'No', 'No', 'No', 'Yes', 'Integrated rubber dampeners',
 'Muted', 'Light', 'Office', NULL, 'approved'),

-- Silent Tactile
('gazzew-zilent-v2', 'Gazzew Zilent V2', (SELECT id FROM designers WHERE name='Gazzew'),
 'Zilent', 'V2 62g', 'Outemu', 'Mechanical MX', 'Silent Tactile',
 'A quiet tactile switch designed by Gazzew, sharing the Boba housing.', '2019-01-01',
 NULL, NULL, 62.0, NULL, 2.00, 3.50, NULL, 'Unknown',
 'Proprietary blend', 'Proprietary blend', 'Unknown', 'Unknown', 'Unknown', 5,
 'No', 'No', 'No', 'Yes', 'Dampened stem',
 'Muted', 'Tactile', 'Quiet Setup', NULL, 'approved');

-- ---------------------------------------------------------------------------
-- blog_posts  (3 beginner articles — PRD §9.1)
-- ---------------------------------------------------------------------------
DELETE FROM blog_posts;

INSERT INTO blog_posts (slug, title, category, tags, excerpt, content, cover_image_url, status, published_at) VALUES
('switch-types-explained',
 'Switch Types Explained: Linear, Tactile, and Clicky',
 'Basics', 'beginner,switch types',
 'Linear, tactile, or clicky? Here is how to choose a starting point.',
 '<p>Mechanical switches come in three main families.</p><h2>Linear</h2><p>Smooth from top to bottom, with no bump. Great for gaming and fast typing.</p><h2>Tactile</h2><p>A small bump tells you the key has actuated. Popular for typing.</p><h2>Clicky</h2><p>Like tactile, but with an added audible click. Fun, but loud.</p>',
 NULL, 'published', NOW()),

('what-affects-keyboard-sound',
 'What Affects Keyboard Sound?',
 'Sound', 'beginner,sound',
 'Why the same switch can sound completely different in two keyboards.',
 '<p>The switch is only part of the story. Sound also depends on:</p><ul><li>Case material and size</li><li>Plate material</li><li>Mounting style</li><li>Foam and fillers</li><li>Keycap material and profile</li></ul><p>That is why a switch can sound "thocky" in one board and "clacky" in another.</p>',
 NULL, 'published', NOW()),

('keyboard-foams-and-fillers-explained',
 'Keyboard Foams and Fillers Explained',
 'Sound', 'beginner,foam',
 'Case foam, plate foam, and other fillers — what they do.',
 '<p>Fillers reduce hollowness and tune sound.</p><h2>Case foam</h2><p>Sits at the bottom of the case to dampen echo.</p><h2>Plate foam</h2><p>Sits between the plate and PCB to soften the sound.</p><h2>Other fillers</h2><p>Materials like PE foam and tape mods change the sound further.</p>',
 NULL, 'published', NOW());
