<?php

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

// Default Global Selections
use App\Models\Selections\{SelectBank,
                           SelectCountry,
                           SelectCurrency,
                           SelectLanguage};

// Default Individual(s) Selections
use App\Models\Selections\{SelectSalutation,
                           SelectGender,
                           SelectRace,
                           SelectMaritalStatus,
                           SelectResidencyStatus,
                           SelectEmploymentStatus,
                           SelectEducationalLevel,
                           SelectAddressType,
                           SelectContactType,
                           SelectRelationshipType};

// Default AA/LFA -- for AA inception date replacements
use App\Models\Selections\RefAAPolicyDates;

// Default LFA Selections
use App\Models\Selections\LegacyFA\{SelectProvider,
                                    SelectOnboardingStatus,
                                    SelectRNFStatus,
                                    SelectAllowanceScheme,
                                    SelectDesignation,
                                    SelectTeamType,
                                    SelectTeamRole,
                                    SelectClientType,
                                    SelectPayrollCategory,
                                    SelectPayrollFeedMapping,
                                    SelectPayrollFeedType,
                                    SelectProductCategory,
                                    SelectProductCoverage,
                                    SelectNomineeStatus,
                                    SelectNomineeBenefit,
                                    SelectSubmissionCategory,
                                    SelectSubmissionProvider,
                                    SelectSubmissionStatus,
                                    SelectPaymentMode,
                                    SelectClientSource,
                                    SelectSalesActivity,
                                    SelectSalesStage,
                                    SelectLeadStage,
                                    SelectOutcome,
                                    SelectTaskType};

class data_SelectionsSeeder extends Seeder
{
  // Default Individual(s) Selections
  private $genders = ['Male', 'Female'];
  private $marital_status = ['Single', 'Married', 'Separated', 'Divorced', 'Widowed'];
  private $race = ['Chinese', 'Malay', 'Indian', 'Eurasian', 'European'];
  private $salutations = ['Mr.', 'Dr.', 'Jr.', 'Prof.', 'Ms.', 'Mdm.', 'Mrs.'];
  private $residency_status = ['Singaporean', 'Singapore PR', 'Employment Pass', 'Personalised Employment Pass', 'Training Employment Pass', 'Entre Pass', 'S Pass', 'Work Permit', 'Work Holiday Pass', "Dependent's Pass", 'Long Term Visit Pass', 'Student Pass'];
  private $employment_status = ['Full-Time', 'Part-Time', 'Freelance', 'Temporary', 'Self-Employed', 'Retired', 'Unemployed'];
  private $educational_levels = ["PSLE", "GCE 'N Levels", "GCE 'O' Levels", "GCE 'A' Levels", "Nitec/Higher Nitec", "Diploma", "Higher Diploma", "Bachelor Degree", "Master Degree", "PhD"];

  // Default LFA Selections
  private $lfa_rnf_status = ['Appointed', 'Pending Submission', 'Submitted', 'Approved', 'Rejected', 'Withdrawn', 'Pending Cessation', 'Ceased', 'Ceased - Resigned', 'Ceased - Terminated'];
  private $lfa_onboarding_status = ['Shortlisted from Job Portal(s)', 'Arranged for Interview', 'Pending Follow-up', 'Taking Examinations', 'Dropped Out', 'On-Boarded', 'Off-Boarded', 'Pending On-Boarding', 'Pending Off-Boarding'];
  private $lfa_allowance_schemes = ['AVIVA - Elite Scheme (6 months)', 'AVIVA - Elite Scheme (12 months)'];
  private $lfa_designations = [
    [
      'title' => 'Chief Executive Officer (CEO)',
      'salesforce' => true,
      'salesforce_tier' => 3,
      'override' => true
    ],
    ['title' => 'Chief Operating Officer (COO)'],
    ['title' => 'Chief Technology Officer (CTO)'],
    ['title' => 'Director, Human Resources'],
    ['title' => 'Director, Risk & Compliance'],
    ['title' => 'Compliance Executive'],
    ['title' => 'Relationship Manager'],
    ['title' => 'Accountant'],
    ['title' => 'Accounts Executive'],
    ['title' => 'IT Manager'],
    ['title' => 'Project Executive'],
    ['title' => 'Admin Manager'],
    ['title' => 'Admin Executive'],
    ['title' => 'Admin Staff'],
    ['title' => 'Business Development Executive'],
    ['title' => 'GI Specialist'],
    ['title' => 'GI Operations Executive'],
    [
      'title' => 'Senior Financial Services Director',
      'salesforce' => true,
      'salesforce_tier' => 3,
      'manager_or_self' => 0,
      'manager_or_agent' => 0,
      'override' => true
    ],
    [
      'title' => 'Financial Services Director',
      'salesforce' => true,
      'salesforce_tier' => 3,
      'manager_or_self' => 0,
      'manager_or_agent' => 0,
      'override' => true
    ],
    [
      'title' => 'Financial Services Director (Provisional)',
      'salesforce' => true,
      'salesforce_tier' => 2,
      'manager_or_self' => 15,
      'manager_or_agent' => 30,
      'override' => true
    ],
    [
      'title' => 'Assistant Financial Services Director',
      'salesforce' => true,
      'salesforce_tier' => 2,
      'manager_or_self' => 25,
      'manager_or_agent' => 25,
      'override' => true
    ],
    [
      'title' => 'Financial Services Manager',
      'salesforce' => true,
      'salesforce_tier' => 2,
      'manager_or_self' => 15,
      'manager_or_agent' => 15,
      'override' => true
    ],
    [
      'title' => 'Financial Services Manager (Salaried)',
      'salesforce' => true,
      'salesforce_tier' => 2,
      'manager_or_self' => 0,
      'manager_or_agent' => 0
    ],
    [
      'title' => 'Salaried Manager',
      'salesforce' => true,
      'salesforce_tier' => 2,
      'manager_or_self' => 0,
      'manager_or_agent' => 0
    ],
    [
      'title' => 'Associate Manager',
      'salesforce' => true,
      'salesforce_tier' => 1
    ],
    [
      'title' => 'Senior Financial Services Consultant',
      'salesforce' => true,
      'salesforce_tier' => 1
    ],
    [
      'title' => 'Premier Financial Services Consultant',
      'salesforce' => true,
      'salesforce_tier' => 1
    ],
    [
      'title' => 'Executive Financial Services Consultant',
      'salesforce' => true,
      'salesforce_tier' => 1
    ],
    [
      'title' => 'Financial Services Consultant',
      'salesforce' => true,
      'salesforce_tier' => 1
    ],
  ];
  private $lfa_team_roles = ['Counsellor', 'Trainer', 'Supervisor', 'Leader', 'Member'];
  private $lfa_team_types = [
    [
      'slug' => 'collab',
      'title' => 'Collaboration'
    ],
    [
      'slug' => 'unit',
      'title' => 'Sales - Managerial Unit'
    ],
    [
      'slug' => 'group',
      'title' => 'Sales - Director Group'
    ],
    [
      'slug' => 'doorknock',
      'title' => 'Doorknock'
    ],
    [
      'slug' => 'roadshow',
      'title' => 'Roadshow'
    ],
    [
      'slug' => 'payroll',
      'title' => 'Payroll'
    ]
  ];
  private $lfa_client_types = ['Individual', 'Business'];
  private $contact_types = ['Default', 'Emergency'];
  private $address_types = ['Residential', 'Mailing', 'Work'];
  private $relationship_types = [
    'Acquaintance',
    'Associate',
    'Colleague',
    'Dependent',
    'Family',
    'Friend',
    'Spouse'
  ];
  private $lfa_nominee_status = ['Accepted', 'Pending Contact', 'On-going Discussion(s)', 'Not Interested'];
  private $lfa_nominee_benefits = [
    [
      'slug' => 'benefit',
      'title' => 'Pay the introducer fee(s) to nominee.'
    ],
    [
      'slug' => 'receive',
      'title' => 'Receive the introducer fee(s).'
    ],
    [
      'slug' => 'donate',
      'title' => 'Donate the introducer fee(s) to charity.'
    ]
  ];

  private $providers = [
    [
      'name' => 'Aetna Insurance (Singapore) Pte. Ltd.',
      'alias' => 'aetna',
      'color' => '#7a3d94',
      'background' => 'linear-gradient(#af5ad0, #722d8c)',
      'slug' => 'aetna-insurance'
    ],
    [
      'name' => 'AIG Asia Pacific Insurance Pte. Ltd.',
      'alias' => 'aig',
      'color' => '#009ad8',
      'background' => 'linear-gradient(#2ab5ef, #00648e)',
      'slug' => 'aig-asia-pacific-insurance'
    ],
    [
      'name' => 'Australia and New Zealand Banking Group Limited',
      'alias' => 'anz',
      'color' => '#017ab5',
      'background' => 'linear-gradient(#004069,#002238)',
      'slug' => 'australia-and-new-zealand-banking-group'
    ],
    [
      'name' => 'Aviva Ltd.', 'code_length' => 8,
      'alias' => 'aviva',
      'color' => '#f8d203',
      'background' => 'linear-gradient(#ffec7a,#c7ac04)',
      'slug' => 'aviva'
    ],
    [
      'name' => 'AXA Insurance Singapore Pte. Ltd.',
      'alias' => 'axa', 'code_length' => 5,
      'color' => '#00018a',
      'background' => 'linear-gradient(#2c2cd2,#02025f)',
      'slug' => 'axa-insurance'
    ],
    [
      'name' => 'Bank of China Limited',
      'alias' => 'boc',
      'color' => '#ba053c',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'bank-of-china'
    ],
    [
      'name' => 'CIMB Bank Berhad',
      'alias' => 'cimb',
      'slug' => 'cimb-bank'
    ],
    [
      'name' => 'Citibank Singapore Ltd',
      'alias' => 'citi',
      'slug' => 'citibank-singapore'
    ],
    [
      'name' => 'Colab',
      'alias' => 'colab',
      'slug' => 'colab-ventures'
    ],
    [
      'name' => 'PropertyGuru Limited',
      'alias' => 'propertyguru',
      'slug' => 'PropertyGuru-limited'
    ],
    [
      'name' => 'China Taiping Insurance (Singapore) Pte. Ltd',
      'alias' => 'cntaiping',
      'color' => '#0069b2',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'china-taiping-insurance'
    ],
    [
      'name' => 'Chubb Insurance Singapore Limited',
      'alias' => 'chubb',
      'color' => '#130d94',
      'background' => 'linear-gradient(#5441ce,#150098)',
      'slug' => 'chubb-insurance'
    ],
    [
      'name' => 'Cigna Europe Insurance Company S.A.-N.V.',
      'alias' => 'cigna',
      'color' => '#007fc5',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'cigna-europe-insurance'
    ],
    [
      'name' => 'DBS Bank Ltd',
      'alias' => 'dbs',
      'color' => '#e71b23',
      'background' => 'linear-gradient(#333,#000)',
      'slug' => 'dbs-bank'
    ],
    [
      'name' => 'Etiqa Insurance Pte. Ltd.',
      'alias' => 'etiqa',
      'color' => '#f8bd10',
      'background' => 'linear-gradient(#ffc400, #9e7a00)',
      'slug' => 'etiqa-insurance'
    ],
    [
      'name' => 'Friends Provident International Limited',
      'alias' => 'fp', 'code_length' => 10,
      'color' => '#00315f',
      'background' => 'linear-gradient(#1045c1,#021d5a)',
      'slug' => 'friends-provident-international'
    ],
    [
      'name' => 'Havenport Asset Management Pte. Ltd.',
      'alias' => 'havenport', 'code_length' => 12,
      'color' => '#a27b4e',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'havenport-asset-management'
    ],
    [
      'name' => 'Henner Group',
      'alias' => 'henner',
      'color' => '#00328a',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'henner-group'
    ],
    [
      'name' => 'HL Assurance Pte. Ltd.',
      'alias' => 'hlassurance',
      'color' => '#001346',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'hl-assurance'
    ],
    [
      'name' => 'Hong Leong Finance Limited',
      'alias' => 'hongleong',
      'color' => '#881f35',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'hong-leong-finance'
    ],
    [
      'name' => 'Legacy FA Pte. Ltd.',
      'alias' => 'legacy',
      'color' => '#004f9f',
      'background' => 'linear-gradient(#23395a, #1f1d36)',
      'slug' => 'legacy-fa'
    ],
    [
      'name' => 'Liberty Insurance Pte. Ltd.',
      'alias' => 'liberty',
      'color' => '#002560',
      'background' => 'linear-gradient(#11278a,#010d42)',
      'slug' => 'liberty-insurance'
    ],
    [
      'name' => 'Life Insurance Corporation (Singapore) Pte. Ltd.',
      'alias' => 'lic',
      'color' => '#1d4e9e',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'life-insurance-corporation'
    ],
    [
      'name' => 'Malayan Banking Berhad',
      'alias' => 'maybank',
      'color' => '#f8c806',
      'background' => 'linear-gradient(#ffc700,#a98300)',
      'slug' => 'malayan-banking'
    ],
    [
      'name' => 'Manulife (Singapore) Pte Ltd',
      'alias' => 'manulife',
      'color' => '#016638',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'manulife'
    ],
    [
      'name' => 'MSIG Insurance (Singapore) Pte. Ltd.',
      'alias' => 'msig',
      'color' => '#f80100',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'msig-insurance'
    ],
    [
      'name' => 'Navigator Investment Services Ltd.',
      'alias' => 'navigator', 'code_length' => 8,
      'color' => '#f8d203',
      'background' => 'linear-gradient(#ffec7a,#c7ac04)',
      'slug' => 'navigator-investment-services'
    ],
    [
      'name' => 'NTUC Income Insurance Co-Operative Ltd.',
      'alias' => 'ntuc', 'code_length' => 6,
      'color' => '#e87e04',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'ntuc-income-insurance'
    ],
    [
      'name' => 'Oversea-Chinese Banking Corporation Limited',
      'alias' => 'ocbc',
      'color' => '#e72223',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'overseas-chinese-banking-corporation'
    ],
    [
      'name' => 'QBE Insurance (Singapore) Pte. Ltd.',
      'alias' => 'qbe',
      'color' => '#0096de',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'qbe-insurance'
    ],
    [
      'name' => 'Raffles Health Insurance Pte. Ltd.',
      'alias' => 'rhi', 'code_length' => 8,
      'color' => '#027d67',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'raffles-health-insurance'
    ],
    [
      'name' => 'RHB Bank Berhad',
      'alias' => 'rhb',
      'color' => '#005ca6',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'rhb-bank'
    ],
    [
      'name' => 'SimplyWills Pte. Ltd.',
      'alias' => 'simplywills',
      'color' => '#2951dd',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'simply-wills'
    ],
    [
      'name' => 'Singapura Finance Ltd.',
      'alias' => 'sf',
      'color' => '#ed7631',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'singapura-finance'
    ],
    [
      'name' => 'Sompo Insurance Singapore Pte. Ltd.',
      'alias' => 'sompo',
      'color' => '#d4012f',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'sompo-insurance'
    ],
    [
      'name' => 'Standard Chartered Bank (Singapore) Limited',
      'alias' => 'scb',
      'color' => '#006fa5',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'standard-chartered-bank'
    ],
    [
      'name' => 'Swiss Life (Singapore) Pte. Ltd.',
      'alias' => 'swisslife',
      'color' => '#d11f30',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'swiss-life'
    ],
    [
      'name' => 'Tokio Marine Life Insurance Singapore Ltd.',
      'alias' => 'tm', 'code_length' => 7,
      'color' => '#0097d3',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'tokio-marine-life-insurance'
    ],
    [
      'name' => 'Transamerica Life (Bermuda) Ltd.',
      'alias' => 'transamerica',
      'color' => '#dc2425',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'transamerica-life-bermuda'
    ],
    [
      'name' => 'United Overseas Bank Limited',
      'alias' => 'uob',
      'color' => '#122253',
      'background' => 'linear-gradient(#fff,#ddd)',
      'slug' => 'united-overseas-bank'
    ],
  ];

  private $payroll_categories = [
    ['title' => 'Health Insurance', 'slug' => 'health'],
    ['title' => 'Life Insurance', 'slug' => 'life'],
    ['title' => 'Health/Life Insurance', 'slug' => 'health-life'],
    ['title' => 'Trailer', 'slug' => 'trailer'],
    ['title' => 'BUPA', 'slug' => 'bupa'],
    ['title' => 'Collective Investment Schemes', 'slug' => 'cis'],
    ['title' => 'General Insurance', 'slug' => 'gi'],
    ['title' => 'Employee Benefits & Healthcare', 'slug' => 'ebh'],
    ['title' => 'Loan Referrals', 'slug' => 'loans'],
    ['title' => 'Unit Trusts', 'slug' => 'unit-trusts'],
    ['title' => 'Wills', 'slug' => 'wills'],
    ['title' => 'TM Group Overriding', 'slug' => 'group-override'],
    ['title' => 'Production Overriding', 'slug' => 'production-override'],
    ['title' => 'Adjustments', 'slug' => 'adjustments'],
    ['title' => 'Elite Scheme', 'slug' => 'elite-scheme'],
    ['title' => 'Incentives', 'slug' => 'incentives'],
    ['title' => 'Introducer Scheme', 'slug' => 'introducer'],
  ];

  private $payroll_types = [
    [
      'title' => 'AA - Health - Basic Feed',
      'era' => 'aa',
      'type' => 'basic',
      'category' => 'health',
      'date_format' => 'Ymd',
      'mapping' => 1,
      'provider' => 'aviva'
    ],
    [
      'title' => 'AA - Life - Basic Feed',
      'era' => 'aa',
      'type' => 'basic',
      'category' => 'life',
      'date_format' => 'Ymd',
      'mapping' => 1,
      'provider' => 'aviva'
    ],
    [
      'title' => 'AA - Health - Overriding Feed',
      'era' => 'aa',
      'type' => 'or',
      'category' => 'health',
      'date_format' => 'Ymd',
      'mapping' => 1,
      'provider' => 'aviva'
    ],
    [
      'title' => 'AA - Life - Overriding Feed',
      'era' => 'aa',
      'type' => 'or',
      'category' => 'life',
      'date_format' => 'Ymd',
      'mapping' => 1,
      'provider' => 'aviva'
    ],
    [
      'title' => 'AA - Trailer Feed',
      'era' => 'aa',
      'type' => 'trailer',
      'category' => 'trailer',
      'date_format' => 'Ymd',
      'mapping' => 1,
      'provider' => 'aviva'
    ],
    [
      'title' => 'RHI - BUPA Feed',
      'era' => 'aa',
      'type' => 'bupa',
      'category' => 'bupa',
      'date_format' => 'Ymd',
      'mapping' => 2,
      'provider' => 'rhi',
      'gst_included' => true
    ],
    [
      'title' => 'AVIVA - Health and Life Feed',
      'era' => 'lfa',
      'category' => 'health-life',
      'date_format' => 'd/m/Y',
      'mapping' => 3,
      'provider' => 'aviva',
    ],
    [
      'title' => 'AVIVA - Navigator Feed',
      'era' => 'lfa',
      'category' => 'cis',
      'date_format' => 'd/m/Y',
      'mapping' => 4,
      'provider' => 'navigator',
    ],
    [
      'title' => 'AXA - Health and Life Feed (Type #1)',
      'era' => 'lfa',
      'category' => 'health-life',
      'type' => 1,
      'date_format' => 'd-M-Y',
      'mapping' => 5,
      'provider' => 'axa',
    ],
    [
      'title' => 'AXA - Health and Life Feed (Type #2)',
      'era' => 'lfa',
      'category' => 'health-life',
      'type' => 2,
      'date_format' => 'Ymd',
      'mapping' => 6,
      'provider' => 'axa',
    ],
    [
      'title' => 'AXA - Trailer Feed (Type #1)',
      'era' => 'lfa',
      'category' => 'trailer',
      'type' => 1,
      'date_format' => 'n/j/Y',
      'mapping' => 7,
      'provider' => 'axa',
    ],
    [
      'title' => 'AXA - Trailer Feed (Type #2)',
      'era' => 'lfa',
      'category' => 'trailer',
      'type' => 2,
      'date_format' => 'Ymd',
      'mapping' => 8,
      'provider' => 'axa',
    ],
    [
      'title' => 'Friends Provident - Life Feed',
      'era' => 'lfa',
      'category' => 'life',
      'date_format' => 'd/m/Y',
      'mapping' => 9,
      'provider' => 'fp',
      'gst_included' => true
    ],
    [
      'title' => 'Tokio Marine - Life Feed',
      'era' => 'lfa',
      'category' => 'life',
      'date_format' => 'j/n/y',
      'mapping' => 10,
      'provider' => 'tm',
    ],
    [
      'title' => 'Tokio Marine - Group Overriding Feed',
      'era' => 'lfa',
      'category' => 'group-override',
      'date_format' => 'j/n/y',
      'mapping' => 11,
      'provider' => 'tm',
    ],
    [
      'title' => 'LIC - Life Feed',
      'era' => 'lfa',
      'category' => 'life',
      'date_format' => 'd/m/Y',
      'mapping' => 12,
      'provider' => 'lic',
    ],
    [
      'title' => 'NTUC - Health Feed (ES/IS)',
      'era' => 'lfa',
      'category' => 'health',
      'date_format' => 'j/n/y',
      'mapping' => 13,
      'provider' => 'ntuc',
    ],
    [
      'title' => 'NTUC - Life Feed',
      'era' => 'lfa',
      'category' => 'life',
      'date_format' => 'Y-m-d',
      'mapping' => 14,
      'provider' => 'ntuc',
    ],
    [
      'title' => 'Havenport - CIS Feed',
      'era' => 'lfa',
      'category' => 'cis',
      'date_format' => 'd/m/Y',
      'mapping' => 15,
      'provider' => 'havenport',
      'gst_included' => true
    ],
    [
      'title' => 'Raffles Health - Health Feed',
      'era' => 'lfa',
      'category' => 'health',
      'date_format' => 'd/m/Y',
      'mapping' => 16,
      'provider' => 'rhi'
    ],
    [
      'title' => 'AVIVA - EBH Feed',
      'era' => 'lfa',
      'category' => 'ebh',
      'date_format' => 'd/m/Y',
      'mapping' => 17,
      'provider' => 'aviva',
    ],
    [
      'title' => 'General Insurance Feed',
      'era' => 'gi',
      'category' => 'gi',
      'date_format' => 'd/m/Y',
      'mapping' => 18,
    ],
    [
      'title' => 'LegacyFA - Adjustments Feed',
      'era' => 'adjustments',
      'date_format' => 'd/m/Y',
      'category' => 'adjustments',
      // 'is_active_check' => 'date_inception',
      // 'mapping' => 19,
    ],
    [
      'title' => 'AA - Navigator - Feed',
      'era' => 'aa',
      'type' => 'fee',
      'category' => 'cis',
      'date_format' => 'Ymd',
      'mapping' => 19,
      'provider' => 'navigator'
    ],
    [
      'title' => 'AA - Navigator - Trailer',
      'era' => 'aa',
      'type' => 'trailer',
      'category' => 'cis',
      'date_format' => 'Ymd',
      'mapping' => 20,
      'provider' => 'navigator'
    ],
    [
      'title' => 'Manulife - Life Feed (Type #1)',
      'era' => 'lfa',
      'category' => 'life',
      'date_format' => 'j/n/y',
      'mapping' => 21,
      'type' => 1,
      'provider' => 'manulife',
    ],
    [
      'title' => 'Manulife - Life Feed (Type #2)',
      'era' => 'lfa',
      'category' => 'life',
      'date_format' => 'j/n/y',
      'mapping' => 22,
      'type' => 2,
      'provider' => 'manulife',
    ],
    [
      'title' => 'China Taiping - Feed',
      'era' => 'lfa',
      'category' => 'life',
      'date_format' => 'd-m-Y',
      'mapping' => 23,
      'provider' => 'cntaiping',
    ],
  ];


  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    /** ===================================================================================================
     * Default Banks
     */
    $this->banks = json_decode(Storage::get('seeders/banks.json'));
    $this->command->info('Seeding Default Selections :: Banks');
    foreach ($this->banks as $data) {
      SelectBank::firstOrCreate(['full_name' => Common::trimString($data->name)], [
        'address' => $data->address,
        'contact' => $data->contact
      ]);
    }

    /** ===================================================================================================
     * Default Countries, Currencies, Languages
     */
    $this->countries = json_decode(Storage::get('seeders/countries.json'));
    $this->command->info('Seeding Default Selections :: Countries, Currencies and Languages');
    foreach ($this->countries as $data) {
      $country = SelectCountry::firstOrCreate(['title' => Common::trimString($data->name)], [
        'nativeName' => $data->nativeName,
        'numericCode' => $data->numericCode,
        'nationality' => $data->demonym,
        'alpha2Code' => $data->alpha2Code,
        'alpha3Code' => $data->alpha3Code,
        'capital' => $data->capital,
        'region' => $data->region,
        'subregion' => $data->subregion,
        'flag' => $data->flag
      ]);

      foreach ($data->currencies as $c) {
        if ($c->code) {
          $currency = SelectCurrency::firstOrCreate(['code' => $c->code], [
            'title' => $c->name,
            'symbol' => $c->symbol
          ]);
          if ($country->currencies()->where('currency_id', $currency->id)->doesntExist()) $country->currencies()->attach($currency);
        }
      }

      foreach ($data->languages as $l) {
        $language = SelectLanguage::firstOrCreate(['title' => $l->name], [
          'nativeName' => $l->nativeName,
          'iso639_1' => $l->iso639_1,
          'iso639_2' => $l->iso639_2
        ]);
        if ($country->languages()->where('language_id', $language->id)->doesntExist()) $country->languages()->attach($language);
      }
    }

    /** ===================================================================================================
     * Default Individual Attribute Selections
     */
    $this->command->info('Seeding Default Selections :: Saluations');
    foreach ($this->salutations as $data) { SelectSalutation::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: Genders');
    foreach ($this->genders as $data) { SelectGender::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: Race');
    foreach ($this->race as $data) { SelectRace::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: Marital Status');
    foreach ($this->marital_status as $data) { SelectMaritalStatus::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: Residency Status');
    foreach ($this->residency_status as $data) { SelectResidencyStatus::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: Employment Status');
    foreach ($this->employment_status as $data) { SelectEmploymentStatus::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: Education Levels');
    foreach ($this->educational_levels as $data) { SelectEducationalLevel::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: Address Types');
    foreach ($this->address_types as $data) { SelectAddressType::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: Contact Types');
    foreach ($this->contact_types as $data) { SelectContactType::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: Relationship Types');
    foreach ($this->relationship_types as $data) { SelectRelationshipType::firstOrCreate(['title' => $data]); }

    /** ===================================================================================================
     * Default AA/LFA -- for AA inception date replacements
     * _aa_policy_dates
     */
    $this->aa_dates = json_decode(Storage::get('seeders/aa-policy-dates-replacement.json'));
    $this->command->info('Seeding Default Selections :: AA Policy Dates');
    foreach ($this->aa_dates as $data) {
      RefAAPolicyDates::create([
        'policy_number' => $data->policy_number,
        'agent_code' => $data->agent_code,
        'type' =>   $data->type,
        'old_date' => ($data->old_date) ? Carbon::createFromFormat('d/m/Y', $data->old_date) : null,
        'new_date' => ($data->new_date) ? Carbon::createFromFormat('d/m/Y', $data->new_date) : null
      ]);
    }

    /** ===================================================================================================
     * Default LFA Providers
     */
    $this->command->info('Seeding Default Selections :: LFA Providers');
    foreach ($this->providers as $data) {
      SelectProvider::firstOrCreate([
        'full_name' => $data['name'],
        'alias' => $data['alias']
      ],[
        'slug' => $data['slug'] ?? null,
        'code_length' => $data['code_length'] ?? null,
        'color' => $data['color'] ?? null,
        'background' => $data['background'] ?? null,
      ]);
    }

    /** ===================================================================================================
     * Default LFA Selections
     */
    $this->command->info('Seeding Default Selections :: LFA RNF status');
    foreach ($this->lfa_rnf_status as $data) { SelectRNFStatus::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: LFA Onboarding Status');
    foreach ($this->lfa_onboarding_status as $data) { SelectOnboardingStatus::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: LFA Schemes');
    foreach ($this->lfa_allowance_schemes as $data) { SelectAllowanceScheme::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: LFA Designations');
    foreach ($this->lfa_designations as $data) { SelectDesignation::firstOrCreate($data); }
    $this->command->info('Seeding Default Selections :: LFA Team Roles');
    foreach ($this->lfa_team_roles as $data) { SelectTeamRole::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: LFA Team Types');
    foreach ($this->lfa_team_types as $data) { SelectTeamType::firstOrCreate($data); }
    $this->command->info('Seeding Default Selections :: LFA Client Types');
    foreach ($this->lfa_client_types as $data) { SelectClientType::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: LFA Introducer Nominee Status');
    foreach ($this->lfa_nominee_status as $data) { SelectNomineeStatus::firstOrCreate(['title' => $data]); }
    $this->command->info('Seeding Default Selections :: LFA Introducer Nominee Types');
    foreach ($this->lfa_nominee_benefits as $lfa_nb_data) { SelectNomineeBenefit::create(['title' => $lfa_nb_data['title'], 'slug' => $lfa_nb_data['slug'] ]); }


    /** ===================================================================================================
     * Default LFA Selections - Payroll Categories
     */
    $this->command->info('Seeding Default Selections :: LFA Payroll Categories');
    foreach ($this->payroll_categories as $data) { SelectPayrollCategory::create($data); }

    /** ===================================================================================================
     * Default LFA Selections - Payroll Commission Feeds Mapping
     */
    $this->command->info('Seeding Default Selections :: LFA Payroll - Commission Feeds Mapping');
    // Commission Feeds Mapping #1 :: AA Health/Life (Basic/OR/Trailer) Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_no',
      'policy_holder_name' => 'policy_holder',
      'policy_holder_nric' => 'policyholder_nric_number',
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'policy_number',
      'product_code' => 'policy_type', // MSP/GLH/MYS
      'product_type' => null,
      'product_name' => null,
      'component_code' => 'component_code',
      'component_name' => null,
      'contract_currency' => 'contract_currency',
      'sum_assured' => 'sum_insured',
      'policy_term' => 'policy_term',
      'premium_term' => 'premium_term',
      'payment_frequency' => null, // fr. 'billing_frequency'
      'date_issued' => 'date_of_issue',
      'date_inception' => 'incept_date',
      'date_expiry' => 'policy_expiry_date',
      'payment_currency' => 'payment_currency',
      'total_investment' => null,
      'premium' => 'nett_premium_paid',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => 'premium_comversion_rate',
      'premium_type' => null, // fr. 'billing_frequency' | 'regular'
      'transaction_no' => 'certificate_number',
      'transaction_code' => 'transaction_code',
      'transaction_desc' => 'transaction_desc',
      'date_transaction' => null,
      'date_instalment_from' => 'installment_from_date',
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => 'payment_currency',
      'commission' => 'commissionor',
      'commission_gst' => null,
      'commission_conversion_rate' => 'commission_conversion_rate',
      'date_commission' => 'commission_run_date',
    ]);

    // Commission Feeds Mapping #2 :: AA/RHI BUPA Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'advisor_code',
      'policy_holder_name' => 'policy_holder',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'member_no',
      'product_code' => null,
      'product_type' => null,
      'product_name' => null,
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => 'contract_currency',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null,
      'date_issued' => null,
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => 'contract_currency',
      'total_investment' => null,
      'premium' => 'subscription_paid',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => 'exchange_rate',
      'premium_type' => null, // 'REGULAR'
      'transaction_no' => 'invoice_no',
      'transaction_code' => 'type',
      'transaction_desc' => 'voucher_no',
      'date_transaction' => 'payment_date',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => 'due_date',
      'commission_type' => null,
      'commission_currency' => 'contract_currency',
      'commission' => 'commission_usd',
      'commission_gst' => null,
      'commission_conversion_rate' => 'exchange_rate',
      'date_commission' => 'payment_date',
    ]);

    // Commission Feeds Mapping #3 :: LFA AVIVA (Health/Life) Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_no',
      'policy_holder_name' => 'policy_holder',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'policy_num',
      'product_code' => null, // fr. 'Policy Type'
      'product_type' => null,
      'product_name' => null, // fr. 'Policy Type'
      'component_code' => null, // fr. 'Component'
      'component_name' => null, // fr. 'Component'
      'contract_currency' => 'cont_curr',
      'sum_assured' => 'sum_insured',
      'policy_term' => 'pol_term',
      'premium_term' => null,
      'payment_frequency' => null, // fr. 'Bil Freq'
      'date_issued' => null,
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => 'cont_curr',
      'total_investment' => null,
      'premium' => 'premium_paid',
      'premium_gst' => null,
      'premium_loading' => 'extra_loading',
      'premium_conversion_rate' => 'fx_rate',
      'premium_type' => null, // fr. 'Bil Freq'
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => null,
      'date_transaction' => null,
      'date_instalment_from' => null,
      'date_instalment_to' => 'paid_to_date',
      'date_due' => 'premium_due_date',
      'commission_type' => null, // fr. 'Fee Type'
      'commission_currency' => 'cont_curr',
      'commission' => 'total_comm_on_payment_curr',
      'commission_gst' => null,
      'commission_conversion_rate' => 'fx_rate',
      'date_commission' => null, // fr. 'Premium Due Date'
    ]);

    // Commission Feeds Mapping #4 :: LFA Navigator Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'adviser_code',
      'policy_holder_name' => 'primary_client_name',
      'policy_holder_nric' => null,
      'life_assured_name' => 'secondary_client_name',
      'life_assured_nric' => null,
      'policy_no' => 'sub_account_number',
      'product_code' => 'plan_structure',
      'product_type' => 'producttype',
      'product_name' => null,
      'component_code' => 'aviva_fund_code',
      'component_name' => 'fund_name',
      'contract_currency' => 'contract_currency',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // 'Single'
      'date_issued' => null,
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => 'contract_currency',
      'total_investment' => null,
      'premium' => 'investment_amountaverage_value',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => 'fx_rate',
      'premium_type' => null, // 'Single'
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => 'remuneration_type',
      'date_transaction' => 'trade_date',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => 'contract_currency',
      'commission' => 'remuneration_before_gst_sgd_amount',
      'commission_gst' => null,
      'commission_conversion_rate' => 'fx_rate',
      'date_commission' => 'date_posted',
    ]);

    // Commission Feeds Mapping #5 :: LFA AXA (Health/Life) Commission Feeds (Feed Type #1)
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_code',
      'policy_holder_name' => 'assured_name',
      'policy_holder_nric' => null,
      'life_assured_name' => 'life_assured_name',
      'life_assured_nric' => 'nric',
      'policy_no' => 'policy_number',
      'product_code' => 'plan_code',
      'product_type' => 'flag',
      'product_name' => 'plan_description',
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => null,
      'sum_assured' => null,
      'policy_term' => 'policy_term',
      'premium_term' => 'payment_term',
      'payment_frequency' => null, // fr. 'Payment Mode'
      'date_issued' => 'policy_issue_date',
      'date_inception' => 'commencement_date',
      'date_expiry' => null,
      'payment_currency' => null,
      'total_investment' => null,
      'premium' => null, // fr. 'Non-Linked Premium' + 'Linked Premium' + 'HS/PA Premium'
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null, // fr. 'RP' / 'SP'
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => null,
      'date_transaction' => 'transaction_date',
      'date_instalment_from' => null,
      'date_instalment_to' => 'paid_to_date',
      'date_due' => null,
      'commission_type' => null, // fr. 'FYC' / 'RYC'
      'commission_currency' => null,
      'commission' => 'total_commission',
      'commission_gst' => 'gst',
      'commission_conversion_rate' => null,
      'date_commission' => null, // fr. production_month && production_year
    ]);

    // Commission Feeds Mapping #6 :: LFA AXA (Health/Life) Commission Feeds (Feed Type #2)
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_code',
      'policy_holder_name' => 'assured_name',
      'policy_holder_nric' => 'assured_nric',
      'life_assured_name' => 'life_assured_name',
      'life_assured_nric' => 'life_assured_nric',
      'policy_no' => 'policy_number',
      'product_code' => 'basic_plan_code',
      'product_type' => null, // fr. 'BASIC OR RIDER PLAN'
      'product_name' => 'plan_description_of_policy',
      'component_code' => 'rider_plan_code',
      'component_name' => null,
      'contract_currency' => null,
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // fr. 'POLICY PAYMENT MODE'
      'date_issued' => 'policy_issue_date',
      'date_inception' => 'commencement_date',
      'date_expiry' => null,
      'payment_currency' => null,
      'total_investment' => null,
      'premium' => null, // fr. 'NON-LINKED PremiumMIUM' + 'LINKED PREMIUM'
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null, // fr. 'RP/SP'
      'transaction_no' => null,
      'transaction_code' => 'ac_transaction_code',
      'transaction_desc' => '',
      'date_transaction' => 'policy_trans_date',
      'date_instalment_from' => null,
      'date_instalment_to' => 'policy_paid_to_date',
      'date_due' => null,
      'commission_type' => null, // fr. 'FIRST YEAR COMMISSION' & 'RENEWAL YEAR COMMISSION'
      'commission_currency' => null,
      'commission' => 'total_commission',
      'commission_gst' => 'gst_amount',
      'commission_conversion_rate' => null,
      'date_commission' => null, // fr. 'MONTH WHERE COMPENSATION IS PAID'
    ]);

    // Commission Feeds Mapping #7 :: LFA AXA Trailer Commission Feeds (Feed Type #1)
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_code',
      'policy_holder_name' => 'assured_name',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'policy_number',
      'product_code' => 'plan_code',
      'product_type' => null,
      'product_name' => 'plan_description',
      'component_code' => 'fund_code',
      'component_name' => 'fund_description',
      'contract_currency' => null,
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null,
      'date_issued' => null,
      'date_inception' => 'commencement_date',
      'date_expiry' => null,
      'payment_currency' => null,
      'total_investment' => 'nav',
      'premium' => null,
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => null,
      'date_transaction' => null,
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => null,
      'commission' => 'total',
      'commission_gst' => 'gst',
      'commission_conversion_rate' => null,
      'date_commission' => null,
    ]);

    // Commission Feeds Mapping #8 :: LFA AXA Trailer Commission Feeds (Feed Type #2)
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_code',
      'policy_holder_name' => 'assured_name',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'policy_number',
      'product_code' => 'plan_code',
      'product_type' => null,
      'product_name' => 'plan_description',
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => null,
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null,
      'date_issued' => null,
      'date_inception' => 'commencement_date',
      'date_expiry' => null,
      'payment_currency' => null,
      'total_investment' => 'fund_under_management_in_local_ccy',
      'premium' => null,
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => null,
      'transaction_code' => 'transaction_code',
      'transaction_desc' => null,
      'date_transaction' => null,
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null, // 'Trailer Fee' | 'Renewal'
      'commission_currency' => null,
      'commission' => 'total',
      'commission_gst' => 'gst_amount',
      'commission_conversion_rate' => null,
      'date_commission' => null,
    ]);

    // Commission Feeds Mapping #9 :: LFA Friends Provident Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_no',
      'policy_holder_name' => 'client_name',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'plan_number',
      'product_code' => null,
      'product_type' => null,
      'product_name' => null,
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => 'currency',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // fr. 'Frequency'
      'date_issued' => null,
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => 'currency',
      'total_investment' => null,
      'premium' => 'premium',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => 'exchange_rate',
      'premium_type' => null,
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => 'event_description',
      'date_transaction' => 'transaction_date',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null, // fr. 'Commission Type'
      'commission_currency' => 'currency',
      'commission' => 'earn_amount',
      'commission_gst' => null,
      'commission_conversion_rate' => 'exchange_rate',
      'date_commission' => 'event_date',
    ]);

    // Commission Feeds Mapping #10 :: LFA Tokio Marine Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'adviserno',
      'policy_holder_name' => 'clientname',
      'policy_holder_nric' => 'nric',
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'reference',
      'product_code' => 'plancode',
      'product_type' => null,
      'product_name' => 'planname',
      'component_code' => 'compcode',
      'component_name' => null,
      'contract_currency' => 'currency',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // fr. "BillingFrequency"
      'date_issued' => null,
      'date_inception' => 'commencementdate',
      'date_expiry' => null,
      'payment_currency' => 'currency',
      'total_investment' => null,
      'premium' => 'installmentpremium',
      'premium_gst' => null,
      'premium_loading' => 'loadedinstallmentpremium',
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => null,
      'transaction_code' => 'subaccountcode',
      'transaction_desc' => 'description',
      'date_transaction' => 'effectivedate',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null, // fr. "SubAccount"
      'commission_currency' => 'currency',
      'commission' => 'actualamount',
      'commission_gst' => null,
      'commission_conversion_rate' => null,
      'date_commission' => 'effectivedate',
    ]);

    // Commission Feeds Mapping #11 :: LFA Tokio Marine - Group Overriding Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'adviserno',
      'policy_holder_name' => 'contractowner',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'contractno',
      'product_code' => 'plan',
      'product_type' => null,
      'product_name' => 'plandescription',
      'component_code' => 'componentcode',
      'component_name' => null,
      'contract_currency' => 'currency',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // fr. "BillingFrequency"
      'date_issued' => null,
      'date_inception' => 'commencementdate',
      'date_expiry' => null,
      'payment_currency' => null,
      'total_investment' => null,
      'premium' => null,
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => 'transaction',
      'date_transaction' => 'effectivedate',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null, // fr. "SubAccount"
      'commission_currency' => null,
      'commission' => 'overridingcommission',
      'commission_gst' => null,
      'commission_conversion_rate' => null,
      'date_commission' => 'effectivedate',
    ]);

    // Commission Feeds Mapping #12 :: LFA LIC Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agt_code',
      'policy_holder_name' => 'name_of_la',
      'policy_holder_nric' => null,
      'life_assured_name' => 'name_of_la',
      'life_assured_nric' => null,
      'policy_no' => 'policy_no',
      'product_code' => 'plan',
      'product_type' => null,
      'product_name' => 'plan_name',
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => null,
      'sum_assured' => null,
      'policy_term' => 'term',
      'premium_term' => 'ppt',
      'payment_frequency' => null,
      'date_issued' => null,
      'date_inception' => 'doc',
      'date_expiry' => null,
      'payment_currency' => null,
      'total_investment' => null,
      'premium' => 'prem',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => null,
      'date_transaction' => null,
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => 'due',
      'commission_type' => null,
      'commission_currency' => null,
      'commission' => 'comamt',
      'commission_gst' => null,
      'commission_conversion_rate' => null,
      'date_commission' => 'due',
    ]);

    // Commission Feeds Mapping #13 :: LFA NTUC (ES/IS) Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'repcode',
      'policy_holder_name' => 'insuredproposername',
      'policy_holder_nric' => null,
      'life_assured_name' => 'insuredproposername',
      'life_assured_nric' => null,
      'policy_no' => 'policyno',
      'product_code' => null, // fr. "Plan"
      'product_type' => null,
      'product_name' => null, // fr. "Plan"
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => null,
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null,
      'date_issued' => null,
      'date_inception' => 'entry_date',
      'date_expiry' => null,
      'payment_currency' => null,
      'total_investment' => null,
      'premium' => 'total_premium',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => null,
      'date_transaction' => 'tran_date',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => null,
      'commission' => 'comm',
      'commission_gst' => null,
      'commission_conversion_rate' => null,
      'date_commission' => null,
    ]);

    // Commission Feeds Mapping #14 :: LFA NTUC (Life) Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_code',
      'policy_holder_name' => 'name',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'policy_no',
      'product_code' => 'plan',
      'product_type' => null,
      'product_name' => null,
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => 'currency',
      'sum_assured' => 'sum_assured',
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // fr. "Pay Mode"
      'date_issued' => null,
      'date_inception' => 'entry_date',
      'date_expiry' => null,
      'payment_currency' => 'currency',
      'total_investment' => null,
      'premium' => 'premium',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => 'dncn_no',
      'transaction_code' => 'emp_code',
      'transaction_desc' => 'premiumtype',
      'date_transaction' => null,
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => 'due_date',
      'commission_type' => null, // fr. "premiumtype"
      'commission_currency' => 'currency',
      'commission' => 'commission',
      'commission_gst' => null,
      'commission_conversion_rate' => null,
      'date_commission' => null,
    ]);

    // Commission Feeds Mapping #15 :: LFA Havenport CIS Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'fa_code',
      'policy_holder_name' => 'client_name',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'portfoliocode',
      'product_code' => null,
      'product_type' => null,
      'product_name' => null,
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => 'pf_base_ccy',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null,
      'date_issued' => null,
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => 'pf_base_ccy',
      'total_investment' => 'plan_ccy_gross_amount',
      'premium' => null,
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => null,
      'transaction_code' => 'isin',
      'transaction_desc' => 'fund_name',
      'date_transaction' => 'transaction_date',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => 'pf_base_ccy',
      'commission' => null, // fr. "Advisory Fee" + "Advisory Wrap Fee" + "DIY Wrap Fee" + "Advisory & Administrative Fees"
      'commission_gst' => null,
      'commission_conversion_rate' => null,
      'date_commission' => null,
    ]);

    // Commission Feeds Mapping #16 :: LFA Raffles Health Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_code',
      'policy_holder_name' => 'name_of_policyholder',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'policy_number',
      'product_code' => null,
      'product_type' => null,
      'product_name' => 'productplan',
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => null,
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null,
      'date_issued' => null,
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => null,
      'total_investment' => null,
      'premium' => 'premium_paid_before_gsts_amount',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => null,
      'transaction_code' => 'type',
      'transaction_desc' => 'policy_period',
      'date_transaction' => null,
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => null,
      'commission' => 'commissions',
      'commission_gst' => null,
      'commission_conversion_rate' => null,
      'date_commission' => null,
    ]);

    // Commission Feeds Mapping #17 :: LFA AVIVA EBH Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_no',
      'policy_holder_name' => 'policy_holder',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'policy_no',
      'product_code' => null,
      'product_type' => null,
      'product_name' => 'plan',
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => 'currency',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null,
      'date_issued' => null,
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => 'currency',
      'total_investment' => null,
      'premium' => null, // fr. Life + ANH
      'premium_gst' => 'gst_on_anh_premium',
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => 'transaction_no',
      'transaction_code' => null,
      'transaction_desc' => 'remarks',
      'date_transaction' => 'transaction_date',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => 'currency',
      'commission' => 'commission',
      'commission_gst' => 'gst_on_comm',
      'commission_conversion_rate' => null,
      'date_commission' => 'commission_date',
    ]);

    // Commission Feeds Mapping #18 :: LFA General Insurance Commission Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_no',
      'policy_holder_name' => 'policy_holder',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'policy_no',
      'product_code' => null,
      'product_type' => null,
      'product_name' => 'plan',
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => 'currency',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null,
      'date_issued' => null,
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => 'currency',
      'total_investment' => null,
      'premium' => 'premium',
      'premium_gst' => 'gst_on_premium',
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null,
      'transaction_no' => 'transaction_no',
      'transaction_code' => null,
      'transaction_desc' => 'remarks',
      'date_transaction' => 'transaction_date',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => 'currency',
      'commission' => 'commission',
      'commission_gst' => 'gst_on_comm',
      'commission_conversion_rate' => null,
      'date_commission' => 'commission_date',
    ]);

    // Commission Feeds Mapping #19 :: LFA Adjustments Feeds
    // SelectPayrollFeedMapping::create([
    //   'agent_no' => 'agent_no',
    //   'policy_holder_name' => null,
    //   'policy_holder_nric' => null,
    //   'life_assured_name' => null,
    //   'life_assured_nric' => null,
    //   'policy_no' => null,
    //   'product_code' => null,
    //   'product_type' => null,
    //   'product_name' => null,
    //   'component_code' => null,
    //   'component_name' => null,
    //   'contract_currency' => null,
    //   'sum_assured' => null,
    //   'policy_term' => null,
    //   'premium_term' => null,
    //   'payment_frequency' => null,
    //   'date_issued' => null,
    //   'date_inception' => null,
    //   'date_expiry' => null,
    //   'payment_currency' => null,
    //   'total_investment' => null,
    //   'premium' => null,
    //   'premium_gst' => null,
    //   'premium_loading' => null,
    //   'premium_conversion_rate' => null,
    //   'premium_type' => null,
    //   'transaction_no' => null,
    //   'transaction_code' => 'type',
    //   'transaction_desc' => 'description',
    //   'date_transaction' => 'date',
    //   'date_instalment_from' => null,
    //   'date_instalment_to' => null,
    //   'date_due' => null,
    //   'commission_type' => null,
    //   'commission_currency' => null,
    //   'commission' => 'amount',
    //   'commission_gst' => null,
    //   'commission_conversion_rate' => null,
    //   'date_commission' => 'date',
    // ]);

    // Commission Feeds Mapping #19 :: AA - Navigator Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_code',
      'policy_holder_name' => 'client_name',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'contract_number',
      'product_code' => null,
      'product_type' => 'contract_type',
      'product_name' => null,
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => 'contract_currency',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // fr. 'billing_frequency'
      'date_issued' => 'issue_date',
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => 'payment_currency',
      'total_investment' => null,
      'premium' => null,
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => 'amount_conversion_rate',
      'premium_type' => null, // fr. 'billing_frequency' | 'regular'
      'transaction_no' => null,
      'transaction_code' => 'transaction_code',
      'transaction_desc' => 'fee_description',
      'date_transaction' => 'transaction_date',
      'date_instalment_from' => 'statement_from_date',
      'date_instalment_to' => 'statement_to_date',
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => 'payment_currency',
      'commission' => 'fee_amount',
      'commission_gst' => null,
      'commission_conversion_rate' => 'fee_amount_conversion_rate',
      'date_commission' => null,
    ]);

    // Commission Feeds Mapping #20 :: AA - Navigator Trailer Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_code',
      'policy_holder_name' => 'client_name',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'contract_number',
      'product_code' => null,
      'product_type' => 'contract_type',
      'product_name' => null,
      'component_code' => 'fund_code',
      'component_name' => 'fund_name',
      'contract_currency' => 'contract_currency',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // fr. 'billing_frequency'
      'date_issued' => 'issue_date',
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => 'payment_currency',
      'total_investment' => null,
      'premium' => null,
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => 'fee_amount_conversion_rate',
      'premium_type' => null, // fr. 'billing_frequency' | 'regular'
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => 'fund_manager_name',
      'date_transaction' => 'transaction_date',
      'date_instalment_from' => 'statement_from_date',
      'date_instalment_to' => 'statement_to_date',
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => 'payment_currency',
      'commission' => 'trailer_fee_amount',
      'commission_gst' => null,
      'commission_conversion_rate' => 'fee_amount_conversion_rate',
      'date_commission' => null,
    ]);

    // Commission Feeds Mapping #21 :: LFA - Manulife Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agt_code',
      'policy_holder_name' => 'insrd_nm',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'pol_num',
      'product_code' => 'plan_code',
      'product_type' => null,
      'product_name' => 'plan_name',
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => null,
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // fr. "pmt_mode"
      'date_issued' => 'pol_iss_dt',
      'date_inception' => 'pol_eff_dt',
      'date_expiry' => null,
      'payment_currency' => null,
      'total_investment' => null,
      'premium' => 'prem_amt',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null, // fr. "prem_typ"
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => null,
      'date_transaction' => 'crr_trxn_dt',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => null,
      'commission' => 'total_payout',
      'commission_gst' => null,
      'commission_conversion_rate' => null,
      'date_commission' => null,
    ]);

    // Commission Feeds Mapping #22 :: LFA - Manulife Feeds - Type 2
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agt_code',
      'policy_holder_name' => 'insrd_name',
      'policy_holder_nric' => null,
      'life_assured_name' => null,
      'life_assured_nric' => null,
      'policy_no' => 'pol_num',
      'product_code' => 'plan_code',
      'product_type' => null,
      'product_name' => 'plan_name',
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => 'crcy',
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // fr. "pmt_mode"
      'date_issued' => 'pol_iss_dt',
      'date_inception' => 'pol_eff_dt',
      'date_expiry' => null,
      'payment_currency' => 'crcy',
      'total_investment' => null,
      'premium' => 'prem_amt',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null, // fr. "prem_typ"
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => null,
      'date_transaction' => 'crr_trxn_dt',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null,
      'commission_currency' => 'crcy',
      'commission' => 'total_payout',
      'commission_gst' => null,
      'commission_conversion_rate' => null,
      'date_commission' => null,
    ]);

    // Commission Feeds Mapping #23 :: LFA - China Taiping Feeds
    SelectPayrollFeedMapping::create([
      'agent_no' => 'agent_code',
      'policy_holder_name' => 'owner_name',
      'policy_holder_nric' => 'owner_id_no',
      'life_assured_name' => 'insured_name_1',
      'life_assured_nric' => 'insured_id_no_1',
      'policy_no' => 'policy_number',
      'product_code' => 'product_code',
      'product_type' => null,
      'product_name' => 'plan_name',
      'component_code' => null,
      'component_name' => null,
      'contract_currency' => null,
      'sum_assured' => null,
      'policy_term' => null,
      'premium_term' => null,
      'payment_frequency' => null, // fr. "payment_frequency"
      'date_issued' => 'risk_commencement_date',
      'date_inception' => 'inception_date',
      'date_expiry' => null,
      'payment_currency' => null,
      'total_investment' => null,
      'premium' => 'modal_premium_sgd_eqvt',
      'premium_gst' => null,
      'premium_loading' => null,
      'premium_conversion_rate' => null,
      'premium_type' => null, // fr. "premium_type"
      'transaction_no' => null,
      'transaction_code' => null,
      'transaction_desc' => null,
      'date_transaction' => 'transation_date',
      'date_instalment_from' => null,
      'date_instalment_to' => null,
      'date_due' => null,
      'commission_type' => null, // fr. "payment_type"
      'commission_currency' => null,
      'commission' => 'commission_amount_sgd_eqvt',
      'commission_gst' => null,
      'commission_conversion_rate' => null,
      'date_commission' => 'paid_date',
    ]);

    /** ===================================================================================================
     * Default LFA Selections - Payroll Commission Feeds Types
     */
    $this->command->info('Seeding Default Selections :: LFA Payroll - Commission Feeds Types');
    foreach ($this->payroll_types as $pt_data) {
      SelectPayrollFeedType::updateOrCreate([
        'title' => $pt_data['title'],
        'era' => $pt_data['era'],
        'type' => $pt_data['type'] ?? null,
        'provider_slug' => (isset($pt_data['provider'])) ? SelectProvider::firstAlias($pt_data['provider'])->slug : null,
        'payroll_cat_slug' => $pt_data['category'] ?? null,
        'payroll_mapping_id' => $pt_data['mapping'] ?? null,
        'date_format' => $pt_data['date_format'] ?? null,
        'gst_included' => $pt_data['gst_included'] ?? false,
      ]);
    }

    /** ===================================================================================================
     * Default LFA Submission Categories
     */
    $this->command->info('Seeding Default Selections :: LFA Submission Categories');
    foreach ([
      ['title' => 'Health/Life Insurance', 'slug' => 'insurance'],
      ['title' => 'Investments', 'slug' => 'cis'],
      ['title' => 'General Insurance', 'slug' => 'gi'],
      ['title' => 'Bank Loans (Referrals)', 'slug' => 'loans'],
      ['title' => 'Wills', 'slug' => 'wills']
    ] as $data) {
      $cat = SelectSubmissionCategory::updateOrCreate([
        'title' => $data['title'],
        'slug' => $data['slug'],
      ]);
    }

    /** ===================================================================================================
     * Default LFA Providers - GI/Loans/Wills relationship override
     */
    $this->command->info('Seeding Default Selections :: Providers - GI/Loans/Wills relationship');
    foreach ([
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'aig-asia-pacific-insurance'],
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'aviva'],
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'axa-insurance'],
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'hl-assurance'],
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'msig-insurance'],
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'ntuc-income-insurance'],
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'qbe-insurance'],
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'sompo-insurance'],
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'liberty-insurance'],
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'chubb-insurance'],
      ['submission_cat_slug' => 'gi', 'provider_slug' => 'cigna-europe-insurance'],
      ['submission_cat_slug' => 'wills', 'provider_slug' => 'simply-wills'],

      // ['title' => 'Health/Life Insurance', 'slug' => 'insurance'],
      // ['title' => 'Investments', 'slug' => 'cis'],
      // ['title' => 'General Insurance', 'slug' => 'gi'],has_submission_cat
      // ['title' => 'Bank Loans (Referrals)', 'slug' => 'loans'],
      // ['title' => 'Wills', 'slug' => 'wills'],
    ] as $data) {
      $cat = SelectSubmissionProvider::updateOrCreate([
        'submission_cat_slug' => $data['submission_cat_slug'],
        'provider_slug' => $data['provider_slug'],
      ]);
    }


    /** ===================================================================================================
     * Default LFA Submission Status
     */
    $this->command->info('Seeding Default Selections :: LFA Submission Status');
    foreach ([
      ['step' => 0, 'title' => 'Rejected', 'slug' => 'rejected', 'description' => 'Application Rejected'],
      ['step' => 1, 'title' => 'Draft', 'slug' => 'draft', 'description' => 'Pending Associate Submission'],
      // ['step' => 2, 'title' => 'Pending', 'description' => 'Pending Manager Approval', 'slug' => 'pending-approval'],
      ['step' => 2, 'title' => 'Pending', 'description' => 'Pending Compliance Screening', 'slug' => 'pending-screening'],
      ['step' => 3, 'title' => 'Pending', 'description' => 'Pending Verification', 'slug' => 'pending-verification'],
      ['step' => 4, 'title' => 'Pending', 'description' => 'Pending Submission', 'slug' => 'pending-submission'],
      ['step' => 5, 'title' => 'Submitted', 'description' => 'Submitted to Provider', 'slug' => 'submitted'],
    ] as $data) {
      $cat = SelectSubmissionStatus::updateOrCreate([
        'title' => $data['title'],
        'description' => $data['description'] ?? null,
        'slug' => $data['slug'],
      ], [
        'step' => $data['step']
      ]);
    }


    /** ===================================================================================================
     * Default LFA Payment Modes
     */
    $this->command->info('Seeding Default Selections :: LFA Payment Modes');
    foreach ([
      ['title' => 'AXS Machine', 'slug' => 'axs'],
      ['title' => 'Central Provident Fund (CPF)', 'slug' => 'cpf'],
      ['title' => 'Cheque', 'slug' => 'cheque'],
      ['title' => 'Child Development Account (CDA)', 'slug' => 'cda'],
      ['title' => 'Company Billing', 'slug' => 'company'],
      ['title' => 'Credit Card', 'slug' => 'credit-card'],
      ['title' => 'Direct Debit', 'slug' => 'direct-debit'],
      ['title' => 'GIRO Payment', 'slug' => 'giro'],
      ['title' => 'Internet Banking', 'slug' => 'ibanking'],
      ['title' => 'Online Payment', 'slug' => 'online'],
      ['title' => 'NETS', 'slug' => 'nets'],
      ['title' => 'Supplementary Retirement Scheme (SRS)', 'slug' => 'srs'],
      ['title' => 'Others', 'slug' => 'others'],
    ] as $data) {
      $cat = SelectPaymentMode::updateOrCreate([
        'title' => $data['title'],
        'slug' => $data['slug'],
      ]);
    }


    /** ===================================================================================================
     * Default LFA Opportunity Source
     */
    $this->command->info('Seeding Default Selections :: LFA Client Sources');
    foreach ([
      ['title' => 'Advertisements', 'slug' => 'advertisements'],
      ['title' => 'Call Centre (Self)', 'slug' => 'call-center-self'],
      ['title' => 'Call Centre (Telemarketer)', 'slug' => 'call-center-tm'],
      ['title' => 'Digital Marketing', 'slug' => 'digital-marketing'],
      ['title' => 'Door-to-Door Canvassing', 'slug' => 'door-to-door'],
      ['title' => 'Existing Client', 'slug' => 'client'],
      ['title' => 'Introducer Scheme', 'slug' => 'introducer'],
      ['title' => 'Orphan Leads', 'slug' => 'orphans'],
      ['title' => 'Personal Website', 'slug' => 'personal-website'],
      ['title' => 'Roadshow Events', 'slug' => 'roadshow'],
      ['title' => 'Referrals', 'slug' => 'referrals'],
      ['title' => 'Social Media', 'slug' => 'social-media'],
      ['title' => 'Street Canvassing', 'slug' => 'street'],
      ['title' => 'Warm Market', 'slug' => 'warm-market'],
      ['title' => 'Web Funnels', 'slug' => 'web-funnels'],
      ['title' => 'Others', 'slug' => 'others'],
    ] as $data) {
      $sources = SelectClientSource::updateOrCreate([
        'title' => $data['title'],
        'slug' => $data['slug'],
      ]);
    }



    /** ===================================================================================================
     * Default LFA Product Categories
     */
    $this->command->info('Seeding Default Selections :: LFA Product Categories');
    foreach ([
      ['title' => 'Annuity', 'category' => 'insurance', 'permission' => 'life'],
      ['title' => 'Bundle', 'category' => 'insurance', 'permission' => 'life'],
      ['title' => 'Disability Income', 'category' => 'insurance', 'permission' => 'life'],
      ['title' => 'Endowment', 'category' => 'insurance', 'permission' => 'life'],
      ['title' => 'Personal Accident', 'category' => 'insurance', 'permission' => 'life'],
      ['title' => 'Term', 'category' => 'insurance', 'permission' => 'life'],
      ['title' => 'Retirement', 'category' => 'insurance', 'permission' => 'life'],
      ['title' => 'Universal Life', 'category' => 'insurance', 'permission' => 'life'],
      ['title' => 'Whole Life', 'category' => 'insurance', 'permission' => 'life'],
      ['title' => 'Investment-Linked (ILP)', 'slug' => 'investment-linked',  'category' => 'insurance', 'permission' => 'ilp'],
      ['title' => 'Health Insurance', 'slug' => 'health', 'category' => 'insurance', 'permission' => 'health'],
      ['title' => 'Hospitalization', 'category' => 'insurance', 'permission' => 'medishield'],
      // General Insurance
      ['title' => 'Accident & Health', 'slug' => 'gi-accident-health', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'Commerical Lines', 'slug' => 'gi-commercial-lines', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'Domestic Helper', 'slug' => 'gi-domestic-helper', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'Employee Benefits', 'slug' => 'gi-employee-benefits', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'Foreign Worker', 'slug' => 'gi-foreign-worker', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'Home', 'slug' => 'gi-home', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'Medical', 'slug' => 'gi-medical', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'Motor', 'slug' => 'gi-motor', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'Personal Accident', 'slug' => 'gi-personal-accident', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'Pet', 'slug' => 'gi-pet', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'Travel', 'slug' => 'gi-travel', 'category' => 'gi', 'permission' => 'gi'],
      ['title' => 'General Insurance (Others)', 'slug' => 'gi-others', 'category' => 'gi', 'permission' => 'gi'],
      // Investments
      ['title' => 'Collective Investment Scheme (CIS)', 'slug' => 'cis', 'category' => 'cis', 'permission' => 'cis'],
      // Loans
      ['title' => 'Bank Loans (Referral)', 'slug' => 'loans', 'category' => 'loans', 'permission' => null],
      // Wills
      ['title' => 'Wills', 'slug' => 'wills', 'category' => 'wills', 'permission' => null],
    ] as $data) {
      $cat = SelectProductCategory::updateOrCreate([
        'title' => $data['title'],
        'category' => $data['category'],
        'permission' => $data['permission'],
        'slug' => $data['slug'] ?? null,
      ]);
    }



    /** ===================================================================================================
     * Default LFA Product Coverage
     */
    $this->command->info('Seeding Default Selections :: LFA Product Coverage');
    foreach ([
      ['product_cat' => null, 'slug' => 'death', 'title' => 'Death - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => null, 'slug' => 'tpd', 'title' => 'Total & Permanent Disability - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => null, 'slug' => 'ci', 'title' => 'Critical Illness - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => null, 'slug' => 'eci', 'title' => 'Early Critical Illness - Sum Assured ($)', 'type' => 'money'],

      ['product_cat' => 'personal-accident', 'slug' => 'pa-daily-hospital', 'title' => 'Daily Hospital Income ($)', 'type' => 'money'],
      ['product_cat' => 'personal-accident', 'slug' => 'pa-double-indemnity', 'title' => 'Double Indemnity on Accident Death', 'type' => 'boolean'],
      ['product_cat' => 'personal-accident', 'slug' => 'pa-medical-expenses', 'title' => 'Medical Expenses Benefit ($)', 'type' => 'money'],
      ['product_cat' => 'personal-accident', 'slug' => 'pa-accidental-death', 'title' => 'Accidental Death Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'personal-accident', 'slug' => 'pa-tpd', 'title' => 'Total & Permanent Disability - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'personal-accident', 'slug' => 'pa-temp-disable-sum', 'title' => 'Temporary Total Disablement - Benefit ($)', 'type' => 'money'],
      ['product_cat' => 'personal-accident', 'slug' => 'pa-temp-disable-term', 'title' => 'Temporary Total Disablement - Benefit Term', 'type' => 'number'],
      ['product_cat' => 'personal-accident', 'slug' => 'pa-tcm', 'title' => 'Traditional Chinese Medicine - Benefit ($)', 'type' => 'money'],

      ['product_cat' => 'endowment', 'slug' => 'endowment-lump-maturity', 'title' => 'Lump Maturity Benefit ($)', 'type' => 'money'],
      ['product_cat' => 'endowment', 'slug' => 'endowment-maturity-date', 'title' => 'Maturity Date', 'type' => 'date'],
      ['product_cat' => 'endowment', 'slug' => 'endowment-death', 'title' => 'Death - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'endowment', 'slug' => 'endowment-tpd', 'title' => 'Total & Permanent Disability - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'endowment', 'slug' => 'endowment-ci', 'title' => 'Critical Illness - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'endowment', 'slug' => 'endowment-eci', 'title' => 'Early Critical Illness - Sum Assured ($)', 'type' => 'money'],

      ['product_cat' => 'hospitalization', 'slug' => 'hospitalization-annual-coverage', 'title' => 'Annual Coverage ($)', 'type' => 'money'],
      ['product_cat' => 'hospitalization', 'slug' => 'hospitalization-coverage-area', 'title' => 'Coverage Area', 'type' => 'selection'],
      ['product_cat' => 'hospitalization', 'slug' => 'hospitalization-hospital-ward-type', 'title' => 'Hospital Ward Type', 'type' => 'selection'],
      ['product_cat' => 'hospitalization', 'slug' => 'hospitalization-rider', 'title' => 'Rider', 'type' => 'selection'],

      ['product_cat' => 'disability-income', 'slug' => 'disability-income-replacement', 'title' => 'Monthly Income Replacement Amount ($)', 'type' => 'money'],
      ['product_cat' => 'disability-income', 'slug' => 'disability-death', 'title' => 'Death - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'disability-income', 'slug' => 'disability-tpd', 'title' => 'Total & Permanent Disability - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'disability-income', 'slug' => 'disability-ci', 'title' => 'Critical Illness - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'disability-income', 'slug' => 'disability-eci', 'title' => 'Early Critical Illness - Sum Assured ($)', 'type' => 'money'],

      ['product_cat' => 'retirement', 'slug' => 'retirement-payout', 'title' => 'Retirement Payout Amount ($)', 'type' => 'money'],
      ['product_cat' => 'retirement', 'slug' => 'retirement-payout-frequency', 'title' => 'Retirement Payout Frequency', 'type' => 'selection'],

      ['product_cat' => 'gi-travel', 'slug' => 'travel-delay', 'title' => 'Travel Delay Coverage ($)', 'type' => 'money'],
      ['product_cat' => 'gi-travel', 'slug' => 'travel-baggage-delay', 'title' => 'Baggage Delay Coverage ($)', 'type' => 'money'],
      ['product_cat' => 'gi-travel', 'slug' => 'travel-baggage-loss', 'title' => 'Lost of Bags & Belongings ($)', 'type' => 'money'],
      ['product_cat' => 'gi-travel', 'slug' => 'travel-medical-expenses', 'title' => 'Overseas Medical Expenses Coverage ($)', 'type' => 'money'],
      ['product_cat' => 'gi-travel', 'slug' => 'travel-medical-evacuation', 'title' => 'Emergency Medical Evacuation Coverage ($)', 'type' => 'money'],
      ['product_cat' => 'gi-travel', 'slug' => 'travel-accidental-death', 'title' => 'Accidental Death - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'gi-travel', 'slug' => 'travel-death', 'title' => 'Death - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'gi-travel', 'slug' => 'travel-tpd', 'title' => 'Total & Permanent Disability - Sum Assured ($)', 'type' => 'money'],

      ['product_cat' => 'gi-motor', 'slug' => 'motor-excess', 'title' => 'Excess ($)', 'type' => 'money'],
      ['product_cat' => 'gi-motor', 'slug' => 'motor-coe-parf', 'title' => 'Insure with COE/PARF', 'type' => 'boolean'],
      ['product_cat' => 'gi-motor', 'slug' => 'motor-coverage', 'title' => 'Coverage', 'type' => 'selection'],
      ['product_cat' => 'gi-motor', 'slug' => 'motor-ncd-percent', 'title' => 'No Claims Discount (%)', 'type' => 'number'],
      ['product_cat' => 'gi-motor', 'slug' => 'motor-ncd-protection', 'title' => 'No Claims Discount Protection', 'type' => 'selection'],
      ['product_cat' => 'gi-motor', 'slug' => 'motor-vehicle-sum-assured', 'title' => 'Vehicle - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'gi-motor', 'slug' => 'motor-windscreen-excess', 'title' => 'Windscreen - Excess ($)', 'type' => 'money'],

      ['product_cat' => 'gi-pet', 'slug' => 'pet-accidental-death', 'title' => 'Accidental Death - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'gi-pet', 'slug' => 'pet-accidental-injury', 'title' => 'Accidental Injury - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'gi-pet', 'slug' => 'pet-treatment-non-surgical', 'title' => 'Non Surgical Treatment - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'gi-pet', 'slug' => 'pet-treatment-surgical', 'title' => 'Surgical Treatment - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'gi-pet', 'slug' => 'pet-theft', 'title' => 'Theft - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'gi-pet', 'slug' => 'pet-third-party', 'title' => 'Third Party Liability - Sum Assured ($)', 'type' => 'money'],

      ['product_cat' => 'gi-home', 'slug' => 'home-alternative-accomodation', 'title' => 'Alternative Accomodation - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'gi-home', 'slug' => 'home-household-contents', 'title' => 'Household Contents - Sum Assured ($)', 'type' => 'money'],
      ['product_cat' => 'gi-home', 'slug' => 'home-renovation', 'title' => 'Renovation - Sum Assured ($)', 'type' => 'money'],
    ] as $data) {
      $coverage = SelectProductCoverage::updateOrCreate([
        'product_cat_slug' => $data['product_cat'],
        'slug' => $data['slug'],
        'type' => $data['type'],
      ],[
        'title' => $data['title'],
      ]);
    }


    /** ===================================================================================================
     * Default LFA Sales Activities
     */
    $this->command->info('Seeding Default Selections :: LFA Sales Activities');
    foreach (['Meeting',
              'Phone - Cold Call',
              'Phone - Lead Response',
              'Phone - Referral Call',
              'Phone - Follow-up Call',
              'Video Conference',
              'Text Messaging',
              'Email'] as $title) {
      SelectSalesActivity::updateOrCreate(['title' => $title]);
    }

    /** ===================================================================================================
     * Default LFA Sales Stages
     */
    $this->command->info('Seeding Default Selections :: LFA Sales Stages');
    foreach ([
      ['step' => 1, 'title' => 'New'],
      ['step' => 2, 'title' => 'Information Gathering'],
      ['step' => 3, 'title' => 'Presentation'],
      ['step' => 4, 'title' => 'Follow-up'],
      ['step' => 5, 'title' => 'Closing'],
      ['step' => 6, 'title' => 'Submission'],
      ['step' => 7, 'title' => 'Incepted Policy'],
    ] as $data) {
      SelectSalesStage::updateOrCreate([
        'title' => $data['title']
      ], [
        'step' => $data['step']
      ]);
    }

    /** ===================================================================================================
     * Default LFA Lead Stages
     */
    $this->command->info('Seeding Default Selections :: LFA Lead Stages');
    foreach ([
      ['step' => 1, 'title' => 'New'],
      ['step' => 2, 'title' => 'Contacted'],
      ['step' => 3, 'title' => 'Interested'],
      ['step' => 4, 'title' => 'Converted to Client'],
    ] as $data) {
      SelectLeadStage::updateOrCreate([
        'title' => $data['title']
      ], [
        'step' => $data['step']
      ]);
    }

    /** ===================================================================================================
     * Default LFA Outcomes
     */
    $this->command->info('Seeding Default Selections :: LFA Outcomes');
    foreach (['Interested',
              'Left message',
              'No response',
              'Not interested',
              'Not able to reach'] as $title) {
      SelectOutcome::updateOrCreate(['title' => $title]);
    }


  } // end run
}
