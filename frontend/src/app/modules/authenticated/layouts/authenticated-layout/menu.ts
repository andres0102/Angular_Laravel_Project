export const menuLinks = [
  {
    label: "Personal Dashboard",
    routerLink: "/authenticated/associates/dashboard",
    iconType: "fa-light",
    iconName: "fa-chart-line",
    roles: ['associate'],
  },
  // {
  //   label: "Commission Statements",
  //   routerLink: "/authenticated/associates/payroll/statements",
  //   iconType: "fa-light",
  //   iconName: "fa-file-invoice-dollar",
  //   roles: ['associate'],
  // },
  {
    label: "Financial Advisory",
    routerLink: "/authenticated/associates/business",
    iconType: "fa-light",
    iconName: "fa-briefcase",
    toggle: "close",
    roles: ['associate'],
    submenu:[
      {
        label: "Case Submissions",
        routerLink: "/authenticated/associates/business/case-submissions",
        iconType: "fa-light",
        iconName: "fa-cabinet-filing",
      },
      {
        label: "Client Policies",
        routerLink: "/authenticated/associates/business/client-policies",
        iconType: "fa-light",
        iconName: "fa-file-certificate",
      },
      {
        label: "Products Catalog",
        routerLink: "/authenticated/associates/business/products-catalog",
        iconType: "fa-light",
        iconName: "fa-books",
      },
    ]
  },
  {
    label: "Salesforce Team",
    routerLink: "/authenticated/associates/teams",
    iconType: "fa-light",
    iconName: "fa-users",
    toggle: "close",
    roles: ['team'],
    submenu:[
      {
        label: "Case Submissions",
        routerLink: "/authenticated/associates/teams/case-submissions",
        iconType: "fa-light",
        iconName: "fa-cabinet-filing",
      },
      {
        label: "Submissions Report",
        routerLink: "/authenticated/associates/teams/submissions-report",
        iconType: "fa-light",
        iconName: "fa-file-chart-line",
      },
    ]
  },
  {
    label: "Relationships",
    routerLink: "/authenticated/associates/relationships",
    iconType: "fa-light",
    iconName: "fa-seedling",
    toggle: "close",
    roles: ['associate'],
    submenu:[
      // {
      //   label: "Opportunities",
      //   routerLink: "/authenticated/associates/relationships/opportunities",
      //   iconType: "fa-light",
      //   iconName: "fa-street-view",
      // },
      {
        label: "Clients",
        routerLink: "/authenticated/associates/relationships/clients",
        iconType: "fa-light",
        iconName: "fa-users",
      },
      // {
      //   label: "Introducers Management",
      //   routerLink: "/authenticated/associates/relationships/introducers-management",
      //   iconType: "fa-light",
      //   iconName: "fa-person-sign",
      // },
      // {
      //   label: "Personal Financial Records",
      //   routerLink: "/authenticated/associates/relationships/financial-records",
      //   iconType: "fa-light",
      //   iconName: "fa-file-signature",
      // },
    ]
  },









  {
    label: "Case Submissions",
    details: "Management",
    routerLink: "/authenticated/admin/case-submissions",
    iconType: "fa-light",
    iconName: "fa-briefcase",
    toggle: "close",
    roles: ['admin'],
  },
  {
    label: "Sales Associates",
    details: "Management",
    routerLink: "/authenticated/admin/sales-associates",
    // routerQueryParams: {'f':'active'},
    iconType: "fa-light",
    iconName: "fa-address-card",
    toggle: "close",
    roles: ['admin'],
  },
  {
    label: "Clients",
    details: "Management",
    routerLink: "/authenticated/admin/clients",
    iconType: "fa-light",
    iconName: "fa-street-view",
    toggle: "close",
    roles: ['admin'],
  },
  {
    label: "Incepted Policies",
    details: "Management",
    routerLink: "/authenticated/admin/incepted-policies",
    iconType: "fa-light",
    iconName: "fa-file-certificate",
    toggle: "close",
    roles: ['admin'],
  },
  {
    label: "User Accounts",
    details: "Management",
    routerLink: "/authenticated/admin/user-accounts",
    iconType: "fa-light",
    iconName: "fa-users",
    toggle: "close",
    roles: ['admin'],
  },
  // {
  //   label: "Realtime Analytics",
  //   routerLink: "/realtime-analytics",
  //   iconType: "fa-light",
  //   iconName: "fa-chart-line"
  // },
  // {
  //   label: "Personnel Management",
  //   routerLink: "/personnel",
  //   iconType: "fa-light",
  //   iconName: "fa-id-card",
  //   toggle: "close",
  //   submenu:[
  //     {
  //       label: "Full Personnel Database",
  //       routerLink: "/personnel/database",
  //       iconType: "fa-light",
  //       iconName: "fa-list-alt",
  //     },
  //     {
  //       label: "Corporate Hierarchy",
  //       routerLink: "/personnel/hierarchy",
  //       iconType: "fa-light",
  //       iconName: "fa-sitemap",
  //     },
  //     {
  //       label: "Sales Units (FS. Directors)",
  //       routerLink: "/personnel/units",
  //       iconType: "fa-light",
  //       iconName: "fa-flag",
  //     },
  //     {
  //       label: "Sales Groups (FS. Managers)",
  //       routerLink: "/personnel/groups",
  //       iconType: "fa-light",
  //       iconName: "fa-users",
  //     }
  //   ]
  // },
  // {
  //   label: "Products Management",
  //   routerLink: "/products",
  //   iconType: "fa-light",
  //   iconName: "fa-box-alt",
  //   toggle:"close",
  //   submenu:[
  //     {
  //       label: "Full Products Database",
  //       routerLink: "/products/database",
  //       iconType: "fa-light",
  //       iconName: "fa-list-alt",
  //     },
  //     {
  //       label: "Providers",
  //       routerLink: "/products/providers",
  //       iconType: "fa-light",
  //       iconName: "fa-suitcase",
  //     }
  //   ]
  // },
  // {
  //   label: "Sales Management",
  //   routerLink: "/pfr",
  //   iconType: "fa-light",
  //   iconName: "fa-file-invoice-dollar",
  //   toggle:"close",
  //   submenu:[
  //     {
  //       label: "Full Submissions Database",
  //       routerLink: "/pfr/database",
  //       iconType: "fa-light",
  //       iconName: "fa-list-alt",
  //     },
  //     {
  //       label: "Personal Financial Records",
  //       routerLink: "/pfr/database",
  //       iconType: "fa-light",
  //       iconName: "fa-piggy-bank",
  //     }
  //   ]
  // },
  // {
  //   label: "Payroll Management",
  //   routerLink: "/payroll",
  //   iconType: "fa-light",
  //   iconName: "fa-money-bill",
  //   toggle:"close",
  //   submenu:[
  //     {
  //       label: "Payroll Batch Management",
  //       routerLink: "/payroll/batch",
  //       iconType: "fa-light",
  //       iconName: "fa-database",
  //     },
  //     {
  //       label: "Payroll Deductions",
  //       routerLink: "/payroll/deductions",
  //       iconType: "fa-light",
  //       iconName: "fa-money-check-alt",
  //     },
  //     {
  //       label: "Payroll Reports",
  //       routerLink: "/payroll/reports",
  //       iconType: "fa-light",
  //       iconName: "fa-file-contract",
  //     }
  //   ]
  // },
  // {
  //   label: "CPD Management",
  //   routerLink: "/training",
  //   iconType: "fa-light",
  //   iconName: "fa-chalkboard-teacher",
  //   toggle:"close",
  //   submenu:[
  //     {
  //       label: "CPD Trainings",
  //       routerLink: "/cpd/trainings",
  //       iconType: "fa-light",
  //       iconName: "fa-chalkboard",
  //     },
  //     {
  //       label: "CPD Reports",
  //       routerLink: "/cpd/reports",
  //       iconType: "fa-light",
  //       iconName: "fa-file-contract",
  //     }
  //   ]
  // },
  // {
  //   label: "BSC Management",
  //   routerLink: "/bsc",
  //   iconType: "fa-light",
  //   iconName: "fa-university",
  //   toggle:"close",
  //   submenu:[
  //     {
  //       label: "Measurement Quarters",
  //       routerLink: "/bsc/measurement-quarters",
  //       iconType: "fa-light",
  //       iconName: "fa-calendar-alt",
  //     },
  //     {
  //       label: "BSC Reports",
  //       routerLink: "/bsc/reports",
  //       iconType: "fa-light",
  //       iconName: "fa-file-contract",
  //     }
  //   ]
  // }
];