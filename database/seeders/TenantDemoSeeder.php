<?php

namespace Database\Seeders;

use App\Models\BookingTemplate;
use App\Models\Facility;
use App\Models\PriorityConfig;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * TenantDemoSeeder — Seeds rich demo data (facilities, categories, resources,
 * extra users, priority configs) scoped to a specific tenant.
 *
 * Called from RegisteredUserController when "Populate with demo data" is checked.
 * Can also be run standalone: php artisan db:seed --class=TenantDemoSeeder
 */
class TenantDemoSeeder extends Seeder
{
    // =========================================================================
    //  UNIVERSITY / EDUCATION
    // =========================================================================

    private array $universityfacilities = [
        ['name' => 'Main Library',                    'location' => 'Central Campus, Block A',    'description' => 'Central academic library with 400 seats, silent zones, group rooms, and computer terminals.'],
        ['name' => 'Engineering & Technology Block',  'location' => 'North Campus, Block B',      'description' => 'Modern engineering faculty housing computer science and electronics labs, plus 3 lecture theatres.'],
        ['name' => 'Science Research Complex',        'location' => 'East Campus, Block C',       'description' => 'Research facility with dedicated chemistry, biology, physics and materials science laboratories.'],
        ['name' => 'Humanities & Arts Centre',        'location' => 'South Campus, Block H',      'description' => 'Arts, social sciences and language faculty with seminar rooms and recording studios.'],
        ['name' => 'Student Union Building',          'location' => 'West Campus, Block SU',      'description' => 'Student activity hub with meeting rooms, event space, and student services offices.'],
        ['name' => 'Sports & Recreation Complex',     'location' => 'South-West Campus, Block SR','description' => 'Indoor sports hall, fitness gym, swimming pool changing rooms, and activity studios.'],
        ['name' => 'Medical & Nursing School',        'location' => 'North-East Campus, Block M', 'description' => 'Clinical skills simulation centre, anatomy labs, and nursing practice wards.'],
        ['name' => 'Business School',                 'location' => 'West Campus, Block BS',      'description' => 'Finance, management and economics faculty with trading simulation lab and case study rooms.'],
    ];

    private array $universitycategories = [
        'Lecture Theatres',
        'Seminar Rooms',
        'Study Rooms',
        'Computer Labs',
        'Chemistry Labs',
        'Biology Labs',
        'Physics Labs',
        'Engineering Labs',
        'Medical Simulation Labs',
        'AV & Presentation Equipment',
        'Sports Facilities',
        'Recording Studios',
    ];

    private array $universityresources = [
        // ── Main Library ──────────────────────────────────────────────────────
        ['fac'=>0,'cat'=>'Study Rooms',              'name'=>'Group Study Room A',          'cap'=>6,   'desc'=>'6-person room with 70" HDMI display, whiteboard, and soundproofing.'],
        ['fac'=>0,'cat'=>'Study Rooms',              'name'=>'Group Study Room B',          'cap'=>6,   'desc'=>'6-person room with natural light and acoustic panels.'],
        ['fac'=>0,'cat'=>'Study Rooms',              'name'=>'Group Study Room C',          'cap'=>8,   'desc'=>'8-person collaborative room with dual screens and writable walls.'],
        ['fac'=>0,'cat'=>'Study Rooms',              'name'=>'Individual Pod 1',            'cap'=>1,   'desc'=>'Silent individual study pod with power outlets, USB charging, and privacy screen.'],
        ['fac'=>0,'cat'=>'Study Rooms',              'name'=>'Individual Pod 2',            'cap'=>1,   'desc'=>'Silent individual study pod on quiet 3rd floor.'],
        ['fac'=>0,'cat'=>'Computer Labs',            'name'=>'Library PC Suite',            'cap'=>40,  'desc'=>'40 desktop workstations with dual monitors, MS Office, and SPSS access.'],

        // ── Engineering & Technology Block ────────────────────────────────────
        ['fac'=>1,'cat'=>'Lecture Theatres',         'name'=>'Lecture Theatre ET-101',      'cap'=>120, 'desc'=>'120-seat tiered theatre with 4K laser projector, surround audio, and live-stream capability.'],
        ['fac'=>1,'cat'=>'Lecture Theatres',         'name'=>'Lecture Theatre ET-102',      'cap'=>80,  'desc'=>'80-seat flat-floor lecture room with interactive whiteboard and recording system.'],
        ['fac'=>1,'cat'=>'Seminar Rooms',            'name'=>'Seminar Room ET-201',         'cap'=>30,  'desc'=>'30-seat seminar room with writable glass walls and video conferencing.'],
        ['fac'=>1,'cat'=>'Seminar Rooms',            'name'=>'Seminar Room ET-202',         'cap'=>25,  'desc'=>'25-seat tutorial room with individual power outlets at every seat.'],
        ['fac'=>1,'cat'=>'Computer Labs',            'name'=>'Software Engineering Lab A',  'cap'=>35,  'desc'=>'35 high-spec workstations (32 GB RAM, RTX 4060) for software development and AI modules.'],
        ['fac'=>1,'cat'=>'Computer Labs',            'name'=>'Software Engineering Lab B',  'cap'=>30,  'desc'=>'30 workstations running Linux and Windows dual-boot for OS and networking courses.'],
        ['fac'=>1,'cat'=>'Computer Labs',            'name'=>'Networking & Security Lab',   'cap'=>20,  'desc'=>'20-station lab with Cisco rack equipment, packet analysers, and penetration testing VMs.'],
        ['fac'=>1,'cat'=>'Engineering Labs',         'name'=>'Electronics & PCB Lab',       'cap'=>24,  'desc'=>'24-bench electronics lab with oscilloscopes, signal generators, and soldering stations.'],
        ['fac'=>1,'cat'=>'Engineering Labs',         'name'=>'Robotics & Mechatronics Lab', 'cap'=>16,  'desc'=>'16-station robotics lab with ABB and UR collaborative robot arms, Arduino/Raspberry Pi kits.'],
        ['fac'=>1,'cat'=>'Engineering Labs',         'name'=>'3D Printing & Fabrication Lab','cap'=>12, 'desc'=>'12 FDM and resin 3D printers, laser cutter, CNC router, and vinyl cutter.'],
        ['fac'=>1,'cat'=>'AV & Presentation Equipment','name'=>'Portable Projector Kit 1', 'cap'=>1,   'desc'=>'Full HD portable projector with wireless presentation adapter, carry bag, and HDMI/USB-C cables.'],
        ['fac'=>1,'cat'=>'AV & Presentation Equipment','name'=>'Portable Projector Kit 2', 'cap'=>1,   'desc'=>'4K ultra-short throw projector kit for boardrooms and small lecture spaces.'],

        // ── Science Research Complex ───────────────────────────────────────────
        ['fac'=>2,'cat'=>'Chemistry Labs',           'name'=>'Undergraduate Chemistry Lab A','cap'=>28, 'desc'=>'28-bench wet chemistry lab with fume hoods, balances, rotary evaporators, and safety showers.'],
        ['fac'=>2,'cat'=>'Chemistry Labs',           'name'=>'Undergraduate Chemistry Lab B','cap'=>28, 'desc'=>'28-bench organic synthesis lab with Schlenk lines and inert atmosphere capabilities.'],
        ['fac'=>2,'cat'=>'Chemistry Labs',           'name'=>'Analytical Chemistry Suite',  'cap'=>12, 'desc'=>'HPLC, GC-MS, NMR spectrometer, and UV-Vis spectrophotometer suite for advanced analysis.'],
        ['fac'=>2,'cat'=>'Biology Labs',             'name'=>'Microbiology Lab A',           'cap'=>24, 'desc'=>'24-bench microbiology lab with Class II biosafety cabinets, autoclaves, and incubators.'],
        ['fac'=>2,'cat'=>'Biology Labs',             'name'=>'Cell Culture Lab',             'cap'=>10, 'desc'=>'Sterile cell culture room with CO₂ incubators, laminar flow hoods, and inverted microscopes.'],
        ['fac'=>2,'cat'=>'Biology Labs',             'name'=>'Genomics & Molecular Lab',     'cap'=>16, 'desc'=>'PCR thermocyclers, gel electrophoresis, Nanodrop spectrophotometer, and sequencing preparation station.'],
        ['fac'=>2,'cat'=>'Physics Labs',             'name'=>'Optics & Photonics Lab',       'cap'=>20, 'desc'=>'Optical bench setups, laser systems (Class 3B), spectrometers, and interferometers.'],
        ['fac'=>2,'cat'=>'Physics Labs',             'name'=>'Electronics & Electromagnetism Lab','cap'=>24,'desc'=>'24-bench lab with oscilloscopes, function generators, LCR meters, and Tesla coil demonstrations.'],
        ['fac'=>2,'cat'=>'Physics Labs',             'name'=>'Thermodynamics Lab',           'cap'=>18, 'desc'=>'Heat engine setups, calorimeters, thermal imaging cameras, and pressure-volume apparatus.'],
        ['fac'=>2,'cat'=>'Engineering Labs',         'name'=>'Materials Testing Lab',        'cap'=>14, 'desc'=>'Tensile testing machine, hardness testers, SEM (scanning electron microscope), and XRD system.'],

        // ── Humanities & Arts Centre ──────────────────────────────────────────
        ['fac'=>3,'cat'=>'Seminar Rooms',            'name'=>'Seminar Room HA-101',          'cap'=>25, 'desc'=>'Discussion-style seminar room with horseshoe seating and wall-mounted display.'],
        ['fac'=>3,'cat'=>'Seminar Rooms',            'name'=>'Seminar Room HA-102',          'cap'=>20, 'desc'=>'20-seat tutorial room ideal for language and literature classes.'],
        ['fac'=>3,'cat'=>'Recording Studios',        'name'=>'Media Recording Studio A',     'cap'=>4,  'desc'=>'Professional audio recording booth with condenser microphones, mixing desk, and acoustic treatment.'],
        ['fac'=>3,'cat'=>'Recording Studios',        'name'=>'Podcast & Video Studio',       'cap'=>6,  'desc'=>'Podcast studio with multi-channel mixer, ring lights, 4K camera rigs, and green screen.'],

        // ── Student Union Building ────────────────────────────────────────────
        ['fac'=>4,'cat'=>'Study Rooms',              'name'=>'SU Meeting Room 1',            'cap'=>12, 'desc'=>'12-seat meeting room for student society committees.'],
        ['fac'=>4,'cat'=>'Study Rooms',              'name'=>'SU Meeting Room 2',            'cap'=>8,  'desc'=>'8-seat meeting room with video conferencing for remote collaborations.'],
        ['fac'=>4,'cat'=>'AV & Presentation Equipment','name'=>'Event PA System',           'cap'=>1,  'desc'=>'Full PA system: 2× powered speakers, subwoofer, wireless microphones, and mixing board.'],

        // ── Medical & Nursing School ──────────────────────────────────────────
        ['fac'=>6,'cat'=>'Medical Simulation Labs',  'name'=>'Clinical Skills Lab A',        'cap'=>16, 'desc'=>'16-bed clinical skills simulation ward with high-fidelity patient manikins and vital sign monitors.'],
        ['fac'=>6,'cat'=>'Medical Simulation Labs',  'name'=>'Clinical Skills Lab B',        'cap'=>12, 'desc'=>'12-bed nursing practice lab with IV training arms, wound care stations, and medication trolleys.'],
        ['fac'=>6,'cat'=>'Medical Simulation Labs',  'name'=>'Anatomy & Prosection Suite',   'cap'=>20, 'desc'=>'Prosection tables, plastinated specimen displays, and anatomage virtual dissection tables.'],
        ['fac'=>6,'cat'=>'Medical Simulation Labs',  'name'=>'High-Fidelity Simulation Suite','cap'=>8, 'desc'=>'4 SimMan 3G manikins in ICU scenario rooms with debrief observation gallery.'],

        // ── Business School ───────────────────────────────────────────────────
        ['fac'=>7,'cat'=>'Lecture Theatres',         'name'=>'Business Lecture Theatre BS-1','cap'=>100,'desc'=>'100-seat tiered theatre with integrated voting systems for interactive case studies.'],
        ['fac'=>7,'cat'=>'Computer Labs',            'name'=>'Trading Simulation Lab',       'cap'=>30, 'desc'=>'30 Bloomberg Terminal workstations with live market data feeds for finance programmes.'],
        ['fac'=>7,'cat'=>'Seminar Rooms',            'name'=>'Case Study Room BS-201',       'cap'=>30, 'desc'=>'Harvard-style tiered case study room with central presentation area.'],
    ];

    // =========================================================================
    //  HEALTHCARE
    // =========================================================================

    private array $healthcarefacilities = [
        ['name' => 'Outpatient & Clinics Wing',   'location' => 'Building East, Ground Floor', 'description' => 'GP and specialist consultation suites for non-admitted patients.'],
        ['name' => 'Surgery & Operating Theatres','location' => 'Building North, 2nd Floor',   'description' => 'Sterile operating theatres, anaesthetics prep rooms, and post-op recovery bays.'],
        ['name' => 'Intensive Care Unit (ICU)',   'location' => 'Building North, 3rd Floor',   'description' => 'Level 3 critical care unit with ventilators, monitoring bays, and isolation rooms.'],
        ['name' => 'Diagnostics & Imaging Centre','location' => 'Building West, Basement',     'description' => 'Radiology, medical imaging, and pathology diagnostic services.'],
        ['name' => 'Emergency Department (ED)',   'location' => 'Building South, Ground Floor','description' => 'Accident and emergency admissions, trauma bays, and triage areas.'],
        ['name' => 'Pharmacy & Dispensary',       'location' => 'Building East, 1st Floor',   'description' => 'Inpatient and outpatient pharmacy, including cytotoxic drug preparation suite.'],
        ['name' => 'Maternity & Neonatal Unit',   'location' => 'Building West, 2nd Floor',   'description' => 'Labour suites, delivery rooms, and neonatal intensive care unit.'],
    ];

    private array $healthcarecategories = [
        'Operating Theatres',
        'Consultation Rooms',
        'ICU / Critical Care Bays',
        'Diagnostic Equipment',
        'Emergency Bays',
        'Medical Vehicles',
        'Procedure Rooms',
        'Medical Equipment',
    ];

    private array $healthcareresources = [
        // Outpatient
        ['fac'=>0,'cat'=>'Consultation Rooms','name'=>'Consultation Room OPD-1',   'cap'=>3, 'desc'=>'GP consultation room with examination couch, sphygmomanometer, and ECG machine.'],
        ['fac'=>0,'cat'=>'Consultation Rooms','name'=>'Consultation Room OPD-2',   'cap'=>3, 'desc'=>'Specialist outpatient room with ophthalmoscope and audiometry booth.'],
        ['fac'=>0,'cat'=>'Consultation Rooms','name'=>'Consultation Room OPD-3',   'cap'=>3, 'desc'=>'Gynaecology consultation room with colposcope and ultrasound unit.'],
        ['fac'=>0,'cat'=>'Consultation Rooms','name'=>'Consultation Room OPD-4',   'cap'=>3, 'desc'=>'Dermatology suite with dermoscopy and Woods lamp.'],
        ['fac'=>0,'cat'=>'Procedure Rooms',   'name'=>'Minor Procedure Room 1',    'cap'=>5, 'desc'=>'Sterile minor ops room for biopsies, suturing, and joint injections.'],

        // Surgery
        ['fac'=>1,'cat'=>'Operating Theatres','name'=>'Main Operating Theatre 1',  'cap'=>10,'desc'=>'General and laparoscopic surgery theatre with Da Vinci robotic surgery system.'],
        ['fac'=>1,'cat'=>'Operating Theatres','name'=>'Main Operating Theatre 2',  'cap'=>10,'desc'=>'Orthopaedic theatre with image intensifier and laminar flow ceiling.'],
        ['fac'=>1,'cat'=>'Operating Theatres','name'=>'Cardiac Operating Theatre', 'cap'=>12,'desc'=>'Cardiac and thoracic surgery theatre with heart-lung bypass machine.'],
        ['fac'=>1,'cat'=>'Operating Theatres','name'=>'Endoscopy Suite',           'cap'=>6, 'desc'=>'Gastroscopy and colonoscopy suite with video endoscopy towers.'],

        // ICU
        ['fac'=>2,'cat'=>'ICU / Critical Care Bays','name'=>'ICU Bay A (Beds 1-4)',   'cap'=>4, 'desc'=>'4-bed open ICU bay with Draeger ventilators and multi-parameter monitors.'],
        ['fac'=>2,'cat'=>'ICU / Critical Care Bays','name'=>'ICU Bay B (Beds 5-8)',   'cap'=>4, 'desc'=>'4-bed bay with CRRT machines for renal support.'],
        ['fac'=>2,'cat'=>'ICU / Critical Care Bays','name'=>'Isolation ICU Room 9',   'cap'=>1, 'desc'=>'Single negative-pressure isolation room for infectious critical care patients.'],
        ['fac'=>2,'cat'=>'Medical Equipment',        'name'=>'Mobile Ventilator Unit', 'cap'=>1, 'desc'=>'Portable ICU-grade ventilator for intra-hospital transport.'],

        // Diagnostics
        ['fac'=>3,'cat'=>'Diagnostic Equipment','name'=>'3T MRI Scanner',            'cap'=>1, 'desc'=>'Siemens 3 Tesla MRI with cardiac and neurological imaging packages.'],
        ['fac'=>3,'cat'=>'Diagnostic Equipment','name'=>'64-Slice CT Scanner',        'cap'=>1, 'desc'=>'Philips 64-slice CT with cardiac CTA capability.'],
        ['fac'=>3,'cat'=>'Diagnostic Equipment','name'=>'Digital X-Ray Suite 1',      'cap'=>2, 'desc'=>'DR X-ray room with fluoroscopy capabilities.'],
        ['fac'=>3,'cat'=>'Diagnostic Equipment','name'=>'Digital X-Ray Suite 2',      'cap'=>2, 'desc'=>'Dedicated chest X-ray and portable radiography room.'],
        ['fac'=>3,'cat'=>'Diagnostic Equipment','name'=>'Ultrasound Room 1',          'cap'=>2, 'desc'=>'GE LOGIQ E10 ultrasound with obstetric and vascular probes.'],
        ['fac'=>3,'cat'=>'Diagnostic Equipment','name'=>'Ultrasound Room 2',          'cap'=>2, 'desc'=>'Portable ultrasound unit for point-of-care scanning.'],

        // Emergency
        ['fac'=>4,'cat'=>'Emergency Bays',    'name'=>'Resuscitation Bay 1',         'cap'=>6, 'desc'=>'Full resuscitation bay with crash cart, defibrillator (AED + manual), ventilator, and IV pumps.'],
        ['fac'=>4,'cat'=>'Emergency Bays',    'name'=>'Resuscitation Bay 2',         'cap'=>6, 'desc'=>'Trauma bay with FAST ultrasound, chest drain kit, and massive transfusion protocol stock.'],
        ['fac'=>4,'cat'=>'Emergency Bays',    'name'=>'Triage Bay',                  'cap'=>8, 'desc'=>'8-bay triage assessment area with vital signs stations.'],

        // Vehicles
        ['fac'=>4,'cat'=>'Medical Vehicles',  'name'=>'Ambulance Unit 01',           'cap'=>4, 'desc'=>'Type B emergency ambulance with LifePak 15 defibrillator and drug kit.'],
        ['fac'=>4,'cat'=>'Medical Vehicles',  'name'=>'Ambulance Unit 02',           'cap'=>4, 'desc'=>'Type C high-dependency transfer unit with onboard ventilator.'],
        ['fac'=>4,'cat'=>'Medical Vehicles',  'name'=>'Patient Transport Vehicle 1', 'cap'=>6, 'desc'=>'Non-emergency patient transport minibus with stretcher capability.'],
    ];

    // =========================================================================
    //  CORPORATE / OFFICE
    // =========================================================================

    private array $corporatefacilities = [
        ['name' => 'HQ Tower — Executive Floors',    'location' => 'Floors 10-12, North Tower',  'description' => 'Executive and senior management floor with board rooms and private offices.'],
        ['name' => 'Main Office — Open Plan',         'location' => 'Floors 4-9, North Tower',    'description' => 'Large open-plan collaborative workspace with hot desks, focus pods, and collaboration zones.'],
        ['name' => 'Innovation & Collaboration Hub',  'location' => 'Tech Park, Building 2',      'description' => 'R&D and product innovation centre with design thinking spaces and prototyping lab.'],
        ['name' => 'Training & Conference Centre',    'location' => 'Ground Floor, Annexe',       'description' => 'Dedicated training rooms, a large conference hall, and a reception/catering area.'],
        ['name' => 'Data Centre & IT Suite',          'location' => 'Basement, North Tower',      'description' => 'Secure server room, NOC, and IT support offices.'],
        ['name' => 'South Satellite Office',          'location' => '12 Commerce Drive, Suite 5', 'description' => 'Satellite branch office with meeting rooms and hot desks.'],
    ];

    private array $corporatecategories = [
        'Board Rooms',
        'Meeting Rooms',
        'Hot Desks',
        'Focus Pods',
        'Conference Halls',
        'Training Rooms',
        'AV Equipment',
        'Company Vehicles',
        'Event Spaces',
    ];

    private array $corporateresources = [
        // Executive
        ['fac'=>0,'cat'=>'Board Rooms',    'name'=>'Executive Boardroom A',         'cap'=>20,'desc'=>'20-seat boardroom with 98" 4K display, VC system, and catering service point.'],
        ['fac'=>0,'cat'=>'Board Rooms',    'name'=>'Executive Boardroom B',         'cap'=>14,'desc'=>'14-seat private boardroom with frosted glass privacy and Zoom Rooms integration.'],
        ['fac'=>0,'cat'=>'Meeting Rooms',  'name'=>'Exec Meeting Room — Horizon',   'cap'=>8, 'desc'=>'8-seat premium meeting room with whiteboard wall and panoramic city view.'],

        // Main Office
        ['fac'=>1,'cat'=>'Meeting Rooms',  'name'=>'Meeting Room Alpha',            'cap'=>8, 'desc'=>'8-seat meeting room with 75" interactive display and wireless presentation.'],
        ['fac'=>1,'cat'=>'Meeting Rooms',  'name'=>'Meeting Room Beta',             'cap'=>6, 'desc'=>'6-seat huddle room with Google Meet hardware.'],
        ['fac'=>1,'cat'=>'Meeting Rooms',  'name'=>'Meeting Room Gamma',            'cap'=>6, 'desc'=>'6-seat quiet meeting room with sound masking.'],
        ['fac'=>1,'cat'=>'Meeting Rooms',  'name'=>'Meeting Room Delta',            'cap'=>4, 'desc'=>'4-seat phone/video call booth with noise cancelling microphones.'],
        ['fac'=>1,'cat'=>'Meeting Rooms',  'name'=>'Meeting Room Epsilon',          'cap'=>4, 'desc'=>'Small 4-seat drop-in meeting room, first come first served.'],
        ['fac'=>1,'cat'=>'Hot Desks',      'name'=>'Hot Desk Zone A (Floor 4)',     'cap'=>20,'desc'=>'20 height-adjustable standing desks with dual-monitor rigs. Book by full or half day.'],
        ['fac'=>1,'cat'=>'Hot Desks',      'name'=>'Hot Desk Zone B (Floor 5)',     'cap'=>20,'desc'=>'20 standard hot desks in open collaborative area.'],
        ['fac'=>1,'cat'=>'Hot Desks',      'name'=>'Hot Desk Zone C (Floor 6)',     'cap'=>15,'desc'=>'Quiet focus zone with 15 desks and no-call policy.'],
        ['fac'=>1,'cat'=>'Focus Pods',     'name'=>'Phone Pod 1',                   'cap'=>1, 'desc'=>'Soundproofed single-person call pod with USB charging and ventilation.'],
        ['fac'=>1,'cat'=>'Focus Pods',     'name'=>'Phone Pod 2',                   'cap'=>1, 'desc'=>'Soundproofed call pod — floor 5.'],
        ['fac'=>1,'cat'=>'Focus Pods',     'name'=>'Phone Pod 3',                   'cap'=>1, 'desc'=>'Soundproofed call pod — floor 6.'],
        ['fac'=>1,'cat'=>'AV Equipment',   'name'=>'Portable Conference Camera Kit','cap'=>1, 'desc'=>'Logitech Rally Bar + wireless lapel mics for ad-hoc video conferencing.'],
        ['fac'=>1,'cat'=>'AV Equipment',   'name'=>'Portable Projector Kit A',      'cap'=>1, 'desc'=>'1080p portable projector with wireless HDMI dongle and tripod screen.'],

        // Innovation Hub
        ['fac'=>2,'cat'=>'Meeting Rooms',  'name'=>'Design Thinking Studio',        'cap'=>16,'desc'=>'Moveable furniture, floor-to-ceiling whiteboards, LEGO Serious Play kits, and Post-it walls.'],
        ['fac'=>2,'cat'=>'Meeting Rooms',  'name'=>'Sprint War Room',               'cap'=>10,'desc'=>'Dedicated agile sprint room with Kanban boards, timer systems, and always-on VC screen.'],

        // Training & Conference
        ['fac'=>3,'cat'=>'Conference Halls','name'=>'Grand Conference Hall',        'cap'=>150,'desc'=>'150-seat conference hall with stage, 4K LED wall, professional PA, and live streaming.'],
        ['fac'=>3,'cat'=>'Training Rooms',  'name'=>'Training Room T1',             'cap'=>30, 'desc'=>'30-seat classroom-style training room with interactive whiteboard and PC for each delegate.'],
        ['fac'=>3,'cat'=>'Training Rooms',  'name'=>'Training Room T2',             'cap'=>24, 'desc'=>'24-seat U-shape training room for facilitated workshops.'],
        ['fac'=>3,'cat'=>'Training Rooms',  'name'=>'Training Room T3',             'cap'=>16, 'desc'=>'Small-group training room ideal for coaching and certification courses.'],

        // Vehicles
        ['fac'=>5,'cat'=>'Company Vehicles','name'=>'Pool Car — Toyota Camry 01',  'cap'=>5, 'desc'=>'Hybrid pool car for local client visits. Key from reception.'],
        ['fac'=>5,'cat'=>'Company Vehicles','name'=>'Pool Car — Toyota Camry 02',  'cap'=>5, 'desc'=>'Second hybrid pool car. Requires 24h advance booking.'],
        ['fac'=>5,'cat'=>'Company Vehicles','name'=>'Executive Car — Mercedes E',  'cap'=>4, 'desc'=>'Executive class car for senior management and VIP client transport.'],
        ['fac'=>5,'cat'=>'Company Vehicles','name'=>'Minibus — Ford Transit',      'cap'=>14,'desc'=>'14-seat minibus for team offsites and airport runs.'],
    ];

    // =========================================================================
    //  GOVERNMENT / PUBLIC SECTOR
    // =========================================================================

    private array $governmentfacilities = [
        ['name' => 'Main Civic Centre',           'location' => 'Town Square, Block A',     'description' => 'Primary seat of local government housing council chambers, committee rooms, and public offices.'],
        ['name' => 'Public Service Hall',         'location' => 'Civic Centre, Ground Floor','description' => 'Front-line service counters for licensing, planning, and citizen services.'],
        ['name' => 'Fleet Management Depot',      'location' => 'Industrial Zone, Yard D',  'description' => 'Government vehicle storage, maintenance workshop, and fuel station.'],
        ['name' => 'Community Centre East',       'location' => '42 East Road',             'description' => 'Community meeting hall, function rooms, and sports space for public hire.'],
        ['name' => 'Emergency Services HQ',       'location' => 'Station Road, Block E',    'description' => 'Emergency coordination centre, dispatch room, and briefing facilities.'],
        ['name' => 'IT & Digital Services Hub',   'location' => 'Civic Centre, 2nd Floor',  'description' => 'Government IT services, cybersecurity operations centre, and training room.'],
    ];

    private array $governmentcategories = [
        'Council Chambers',
        'Committee Rooms',
        'Public Meeting Rooms',
        'Government Vehicles',
        'Emergency Vehicles',
        'IT Equipment',
        'Event Spaces',
        'Briefing Rooms',
    ];

    private array $governmentresources = [
        // Civic Centre
        ['fac'=>0,'cat'=>'Council Chambers', 'name'=>'Full Council Chamber',          'cap'=>60, 'desc'=>'60-seat formal council chamber with public gallery (20 seats), Hansard recording system, and live streaming.'],
        ['fac'=>0,'cat'=>'Committee Rooms',  'name'=>'Planning Committee Room',       'cap'=>20, 'desc'=>'20-seat room with large map table, digital planning portal display, and public seating.'],
        ['fac'=>0,'cat'=>'Committee Rooms',  'name'=>'Finance & Audit Committee Room','cap'=>16, 'desc'=>'16-seat committee room with secure document storage and financial data terminals.'],
        ['fac'=>0,'cat'=>'Committee Rooms',  'name'=>'Overview & Scrutiny Room',      'cap'=>14, 'desc'=>'14-seat room for scrutiny panels and select committee hearings.'],
        ['fac'=>0,'cat'=>'Committee Rooms',  'name'=>'Executive Cabinet Room',        'cap'=>12, 'desc'=>'Private cabinet room for senior officer and cabinet member meetings.'],
        ['fac'=>0,'cat'=>'Briefing Rooms',   'name'=>'Senior Officer Briefing Room',  'cap'=>10, 'desc'=>'Confidential briefing room with secure video link to central government.'],
        ['fac'=>0,'cat'=>'Public Meeting Rooms','name'=>'Public Consultation Suite 1','cap'=>30, 'desc'=>'Drop-in consultation space with display boards and feedback stations for public engagement events.'],
        ['fac'=>0,'cat'=>'Public Meeting Rooms','name'=>'Public Consultation Suite 2','cap'=>20, 'desc'=>'Medium consultation room with projection for planning exhibitions and town hall meetings.'],

        // Community Centre East
        ['fac'=>3,'cat'=>'Event Spaces',     'name'=>'Community Hall (Main)',         'cap'=>200,'desc'=>'200-person community hall with stage, PA system, and kitchen access. Available for public and civic events.'],
        ['fac'=>3,'cat'=>'Event Spaces',     'name'=>'Function Room East 1',          'cap'=>50, 'desc'=>'50-person function room for community group meetings and small civic events.'],
        ['fac'=>3,'cat'=>'Event Spaces',     'name'=>'Function Room East 2',          'cap'=>30, 'desc'=>'30-person breakout room with folding furniture for flexible layouts.'],
        ['fac'=>3,'cat'=>'Public Meeting Rooms','name'=>'Residents Forum Room',       'cap'=>40, 'desc'=>'40-seat room dedicated to residents association and ward forum meetings.'],

        // Fleet Management Depot — Vehicles
        ['fac'=>2,'cat'=>'Government Vehicles','name'=>'Council Car 01 — Toyota Prius','cap'=>5, 'desc'=>'Pool hybrid car for officer business travel. Book 48h in advance.'],
        ['fac'=>2,'cat'=>'Government Vehicles','name'=>'Council Car 02 — Toyota Prius','cap'=>5, 'desc'=>'Second pool hybrid car.'],
        ['fac'=>2,'cat'=>'Government Vehicles','name'=>'Council Car 03 — Ford Kuga EV','cap'=>5, 'desc'=>'Electric pool car — charging cable in boot.'],
        ['fac'=>2,'cat'=>'Government Vehicles','name'=>'Enforcement Van 01',           'cap'=>3, 'desc'=>'Enforcement services transit van with CCTV and signage.'],
        ['fac'=>2,'cat'=>'Government Vehicles','name'=>'Refuse Collection Lorry 01',   'cap'=>3, 'desc'=>'HGV refuse collection vehicle. Requires C+E licence.'],
        ['fac'=>2,'cat'=>'Government Vehicles','name'=>'Grounds Maintenance Truck 01', 'cap'=>4, 'desc'=>'Flatbed truck for parks and grounds maintenance crew.'],
        ['fac'=>2,'cat'=>'Government Vehicles','name'=>'Minibus — Ford Transit 16',    'cap'=>16,'desc'=>'16-seat accessible minibus with ramp for community transport.'],

        // Emergency Services
        ['fac'=>4,'cat'=>'Emergency Vehicles','name'=>'Emergency Response Car 01',    'cap'=>4, 'desc'=>'Rapid response car for emergency coordination officers.'],
        ['fac'=>4,'cat'=>'Emergency Vehicles','name'=>'Emergency Response Car 02',    'cap'=>4, 'desc'=>'Backup emergency response vehicle.'],
        ['fac'=>4,'cat'=>'Briefing Rooms',   'name'=>'Emergency Coordination Room',   'cap'=>20,'desc'=>'24/7 Emergency Operations Centre with GIS mapping, radio dispatch, and multi-agency video links.'],
        ['fac'=>4,'cat'=>'Briefing Rooms',   'name'=>'Pre-Operations Briefing Room',  'cap'=>15,'desc'=>'Briefing room for pre-shift operational briefings and incident planning.'],

        // IT Hub
        ['fac'=>5,'cat'=>'IT Equipment',     'name'=>'Laptop Pool — Set A (10 units)','cap'=>10,'desc'=>'10 Dell Latitude laptops with government-standard encryption and MDM enrolled.'],
        ['fac'=>5,'cat'=>'IT Equipment',     'name'=>'Laptop Pool — Set B (10 units)','cap'=>10,'desc'=>'10 HP EliteBook laptops for field officer use.'],
        ['fac'=>5,'cat'=>'IT Equipment',     'name'=>'Portable AV Presentation Kit',  'cap'=>1, 'desc'=>'Portable projector, screen, and wireless clicker for public engagement events.'],
    ];

    // =========================================================================
    //  SECTOR-SPECIFIC COMPOSITE BOOKING TEMPLATES
    // =========================================================================

    private array $universitytemplates = [
        [
            'name' => 'Lecture & Media Presentation Bundle',
            'desc' => 'Book a tiered Lecture Theatre along with portable AV & Presentation Equipment for interactive lectures.',
            'items' => [
                ['cat' => 'Lecture Theatres', 'qty' => 1],
                ['cat' => 'AV & Presentation Equipment', 'qty' => 1],
            ],
        ],
        [
            'name' => 'Science Lab & Seminar Session',
            'desc' => 'Combined booking of an Undergraduate Chemistry/Physics Lab paired with an adjacent Seminar Room and AV kit.',
            'items' => [
                ['cat' => 'Chemistry Labs', 'qty' => 1],
                ['cat' => 'Seminar Rooms', 'qty' => 1],
                ['cat' => 'AV & Presentation Equipment', 'qty' => 1],
            ],
        ],
        [
            'name' => 'Robotics & Software Engineering Workshop',
            'desc' => 'Simultaneous allocation of a high-performance Computer Lab and the Robotics & Mechatronics Lab.',
            'items' => [
                ['cat' => 'Computer Labs', 'qty' => 1],
                ['cat' => 'Engineering Labs', 'qty' => 1],
            ],
        ],
        [
            'name' => 'Medical Simulation & Clinical Debrief Suite',
            'desc' => 'Clinical simulation training ward booked together with a seminar room for pre/post-session clinical debriefs.',
            'items' => [
                ['cat' => 'Medical Simulation Labs', 'qty' => 1],
                ['cat' => 'Seminar Rooms', 'qty' => 1],
            ],
        ],
        [
            'name' => 'Media & Podcast Production Suite',
            'desc' => 'High-fidelity audio recording studio bundled with portable AV and presentation equipment.',
            'items' => [
                ['cat' => 'Recording Studios', 'qty' => 1],
                ['cat' => 'AV & Presentation Equipment', 'qty' => 1],
            ],
        ],
    ];

    private array $healthcaretemplates = [
        [
            'name' => 'Surgical Suite with Dedicated Post-Op ICU Bay',
            'desc' => 'Atomic allocation of a sterile Operating Theatre, reserved ICU critical care bay, and mobile ventilator unit.',
            'items' => [
                ['cat' => 'Operating Theatres', 'qty' => 1],
                ['cat' => 'ICU / Critical Care Bays', 'qty' => 1],
                ['cat' => 'Medical Equipment', 'qty' => 1],
            ],
        ],
        [
            'name' => 'Outpatient Specialist Consultation & Diagnostics',
            'desc' => 'Outpatient consultation suite linked with simultaneous diagnostic imaging equipment (Ultrasound/X-Ray).',
            'items' => [
                ['cat' => 'Consultation Rooms', 'qty' => 1],
                ['cat' => 'Diagnostic Equipment', 'qty' => 1],
            ],
        ],
        [
            'name' => 'Emergency Trauma & Rapid Ambulance Dispatch',
            'desc' => 'Full resuscitation emergency bay prepared alongside a rapid emergency ambulance unit.',
            'items' => [
                ['cat' => 'Emergency Bays', 'qty' => 1],
                ['cat' => 'Medical Vehicles', 'qty' => 1],
            ],
        ],
        [
            'name' => 'Minor Procedure & Point-of-Care Ultrasound',
            'desc' => 'Sterile minor procedure room reserved with portable ultrasound diagnostic machine.',
            'items' => [
                ['cat' => 'Procedure Rooms', 'qty' => 1],
                ['cat' => 'Diagnostic Equipment', 'qty' => 1],
            ],
        ],
    ];

    private array $corporatetemplates = [
        [
            'name' => 'Executive Client Summit & Transport',
            'desc' => 'Executive boardroom with premium video conferencing equipment and VIP company transport vehicle.',
            'items' => [
                ['cat' => 'Board Rooms', 'qty' => 1],
                ['cat' => 'AV Equipment', 'qty' => 1],
                ['cat' => 'Company Vehicles', 'qty' => 1],
            ],
        ],
        [
            'name' => 'All-Hands Conference & Breakout Workshops',
            'desc' => 'Grand conference hall booked simultaneously with a breakout training room and portable presentation kit.',
            'items' => [
                ['cat' => 'Conference Halls', 'qty' => 1],
                ['cat' => 'Training Rooms', 'qty' => 1],
                ['cat' => 'AV Equipment', 'qty' => 1],
            ],
        ],
        [
            'name' => 'Agile Design Sprint & Dedicated Desks',
            'desc' => 'Collaborative design thinking meeting room bundled with a reserved block of hot desks.',
            'items' => [
                ['cat' => 'Meeting Rooms', 'qty' => 1],
                ['cat' => 'Hot Desks', 'qty' => 1],
            ],
        ],
    ];

    private array $governmenttemplates = [
        [
            'name' => 'Full Council Hearing & Committee Deliberation',
            'desc' => 'Formal council chamber paired with a private committee room and secure broadcast IT equipment.',
            'items' => [
                ['cat' => 'Council Chambers', 'qty' => 1],
                ['cat' => 'Committee Rooms', 'qty' => 1],
                ['cat' => 'IT Equipment', 'qty' => 1],
            ],
        ],
        [
            'name' => 'Civic Town Hall & Public Consultation',
            'desc' => 'Community event space reserved alongside public consultation rooms and portable presentation equipment.',
            'items' => [
                ['cat' => 'Event Spaces', 'qty' => 1],
                ['cat' => 'Public Meeting Rooms', 'qty' => 1],
                ['cat' => 'IT Equipment', 'qty' => 1],
            ],
        ],
        [
            'name' => 'Emergency Operations Command & Fleet Dispatch',
            'desc' => '24/7 emergency coordination room allocated with emergency rapid response vehicle.',
            'items' => [
                ['cat' => 'Briefing Rooms', 'qty' => 1],
                ['cat' => 'Emergency Vehicles', 'qty' => 1],
            ],
        ],
    ];

    // =========================================================================
    //  Public entry point — called from RegisteredUserController
    // =========================================================================

    public function seedForTenant(Tenant $tenant, User $adminUser): void
    {
        $sector = $tenant->sector ?? 'university';

        $facilities = $this->seedFacilities($tenant, $sector);
        $categories = $this->seedCategories($tenant, $sector);
        $this->seedResources($tenant, $sector, $facilities, $categories);
        $this->seedBookingTemplates($tenant, $sector, $categories);
        $this->seedExtraUsers($tenant);
        $this->seedPriorityConfigs($tenant);
    }

    // =========================================================================
    //  Standalone Artisan run — seeds ALL existing tenants
    // =========================================================================

    public function run(): void
    {
        foreach (Tenant::withoutGlobalScopes()->get() as $tenant) {
            $admin = User::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('role', 'admin')
                ->first();

            if ($admin) {
                $this->command?->info("Seeding demo data for tenant: {$tenant->name} ({$tenant->sector})");
                $this->seedForTenant($tenant, $admin);
            }
        }
    }

    // =========================================================================
    //  Private helpers
    // =========================================================================

    private function getFacilityDefs(string $sector): array
    {
        return match ($sector) {
            'healthcare' => $this->healthcarefacilities,
            'corporate'  => $this->corporatefacilities,
            'government' => $this->governmentfacilities,
            default      => $this->universityfacilities,
        };
    }

    private function getCategoryDefs(string $sector): array
    {
        return match ($sector) {
            'healthcare' => $this->healthcarecategories,
            'corporate'  => $this->corporatecategories,
            'government' => $this->governmentcategories,
            default      => $this->universitycategories,
        };
    }

    private function getResourceDefs(string $sector): array
    {
        return match ($sector) {
            'healthcare' => $this->healthcareresources,
            'corporate'  => $this->corporateresources,
            'government' => $this->governmentresources,
            default      => $this->universityresources,
        };
    }

    private function seedFacilities(Tenant $tenant, string $sector): array
    {
        $created = [];
        foreach ($this->getFacilityDefs($sector) as $data) {
            $created[] = Facility::withoutGlobalScopes()->firstOrCreate(
                ['name' => $data['name'], 'tenant_id' => $tenant->id],
                array_merge($data, ['tenant_id' => $tenant->id])
            );
        }
        return $created;
    }

    private function seedCategories(Tenant $tenant, string $sector): array
    {
        $created = [];
        foreach ($this->getCategoryDefs($sector) as $name) {
            $created[$name] = ResourceCategory::withoutGlobalScopes()->firstOrCreate(
                ['name' => $name, 'tenant_id' => $tenant->id],
                ['name' => $name, 'tenant_id' => $tenant->id]
            );
        }
        return $created;
    }

    private function seedResources(Tenant $tenant, string $sector, array $facilities, array $categories): void
    {
        foreach ($this->getResourceDefs($sector) as $def) {
            $facility = $facilities[$def['fac']] ?? $facilities[0] ?? null;
            $category = $categories[$def['cat']] ?? null;

            if (! $facility || ! $category) {
                continue;
            }

            Resource::withoutGlobalScopes()->firstOrCreate(
                ['name' => $def['name'], 'tenant_id' => $tenant->id],
                [
                    'name'        => $def['name'],
                    'description' => $def['desc'],
                    'status'      => 'active',
                    'capacity'    => $def['cap'],
                    'tenant_id'   => $tenant->id,
                    'facility_id' => $facility->id,
                    'category_id' => $category->id,
                ]
            );
        }
    }

    private function seedExtraUsers(Tenant $tenant): void
    {
        $extras = [
            ['name' => 'Demo Supervisor',   'email' => "supervisor@{$tenant->subdomain}.demo", 'role' => 'supervisor'],
            ['name' => 'Demo Staff User 1', 'email' => "staff1@{$tenant->subdomain}.demo",     'role' => 'end_user'],
            ['name' => 'Demo Staff User 2', 'email' => "staff2@{$tenant->subdomain}.demo",     'role' => 'end_user'],
        ];

        foreach ($extras as $data) {
            User::withoutGlobalScopes()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'email'             => $data['email'],
                    'password'          => Hash::make('password'),
                    'role'              => $data['role'],
                    'email_verified_at' => now(),
                    'tenant_id'         => $tenant->id,
                ]
            );
        }
    }

    private function seedPriorityConfigs(Tenant $tenant): void
    {
        $weights = [
            ['factor' => 'role_admin',      'weight' => 100.00],
            ['factor' => 'role_supervisor', 'weight' => 50.00],
            ['factor' => 'role_end_user',   'weight' => 10.00],
            ['factor' => 'urgency_flag',    'weight' => 30.00],
            ['factor' => 'resource_demand', 'weight' => 2.00],
        ];

        foreach ($weights as $w) {
            PriorityConfig::firstOrCreate(
                ['tenant_id' => $tenant->id, 'factor' => $w['factor']],
                ['weight' => $w['weight']]
            );
        }
    }

    private function getTemplateDefs(string $sector): array
    {
        return match ($sector) {
            'healthcare' => $this->healthcaretemplates,
            'corporate'  => $this->corporatetemplates,
            'government' => $this->governmenttemplates,
            default      => $this->universitytemplates,
        };
    }

    private function seedBookingTemplates(Tenant $tenant, string $sector, array $categories): void
    {
        foreach ($this->getTemplateDefs($sector) as $tDef) {
            $template = BookingTemplate::withoutGlobalScopes()->firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name'      => $tDef['name'],
                ],
                [
                    'description' => $tDef['desc'],
                ]
            );

            foreach ($tDef['items'] as $itemDef) {
                $category = $categories[$itemDef['cat']] ?? null;
                if (! $category) {
                    continue;
                }

                $template->items()->firstOrCreate(
                    [
                        'category_id' => $category->id,
                    ],
                    [
                        'quantity' => $itemDef['qty'],
                    ]
                );
            }
        }
    }
}

