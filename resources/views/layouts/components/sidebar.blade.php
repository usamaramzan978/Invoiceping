<aside class="app-sidebar sticky" id="sidebar">

    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="{{ route('home') }}">
            <x-logo />
        </a>
    </div>
    <!-- End::main-sidebar-header -->

    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">

        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>
            <ul class="main-menu">

                <!-- ================= MAIN ================= -->
                <li class="slide__category">
                    <span class="category-name">Main</span>
                </li>

                <li class="slide">
                    <a href="{{ route('home') }}" class="side-menu__item">
                        <i class="bx bx-home side-menu__icon"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                <!-- ================= BUSINESS SETUP ================= -->
                <li class="slide__category">
                    <span class="category-name">Business Setup</span>
                </li>

                <li class="slide">
                    <a href="{{ route('business-profile.index') }}" class="side-menu__item">
                        <i class="bx bx-buildings side-menu__icon"></i>
                        <span class="side-menu__label">Business Profile</span>
                    </a>
                </li>

                <!-- ================= INVOICES ================= -->
                <li class="slide__category">
                    <span class="category-name">Invoices</span>
                </li>

                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-receipt side-menu__icon"></i>
                        <span class="side-menu__label">Invoices</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide">
                            <a href="{{ route('invoices.index') }}" class="side-menu__item">All Invoices</a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('invoices.create') }}" class="side-menu__item">Create Invoice</a>
                        </li>
                    </ul>
                </li>

                <li class="slide">
                    <a href="{{ route('clients.index') }}" class="side-menu__item">
                        <i class="bx bx-user side-menu__icon"></i>
                        <span class="side-menu__label">Clients</span>
                    </a>
                </li>

                <!-- ================= REMINDERS ================= -->
                <li class="slide__category">
                    <span class="category-name">Automation</span>
                </li>

                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-alarm side-menu__icon"></i>
                        <span class="side-menu__label">Payment Reminders</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide">
                            <a href="{{ route('reminder-rules.index') }}" class="side-menu__item">Reminder Rules</a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('schedule-reminders.index') }}" class="side-menu__item">Scheduled
                                Reminders</a>
                        </li>
                        <li class="slide">
                            <a href="javascript:void(0);" class="side-menu__item">Reminder Logs</a>
                        </li>
                    </ul>
                </li>

                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-message-rounded-dots side-menu__icon"></i>
                        <span class="side-menu__label">Templates</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide">
                            <a href="{{ route('email.templates.create') }}" class="side-menu__item">
                                <span class="side-menu__label">Email Builder</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('templates.index') }}" class="side-menu__item">All Templates</a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('templates.create') }}" class="side-menu__item">Watsapp Template</a>
                        </li>
                    </ul>
                </li>

                <!-- ================= REPORTS ================= -->
                <li class="slide__category">
                    <span class="category-name">Insights</span>
                </li>

                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-bar-chart-alt-2 side-menu__icon"></i>
                        <span class="side-menu__label">Reports & Export</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide">
                            <a href="javascript:void(0);" class="side-menu__item">Invoice Summary</a>
                        </li>
                        <li class="slide">
                            <a href="javascript:void(0);" class="side-menu__item">Payment Performance</a>
                        </li>
                        <li class="slide">
                            <a href="javascript:void(0);" class="side-menu__item">Export to Excel</a>
                        </li>
                    </ul>
                </li>

                <!-- ================= BILLING ================= -->
                <li class="slide__category">
                    <span class="category-name">Billing</span>
                </li>

                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-credit-card side-menu__icon"></i>
                        <span class="side-menu__label">Subscription</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide">
                            <a href="{{ route('subscriptions.plans.index') }}" class="side-menu__item">View Plans</a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('subscriptions.index') }}" class="side-menu__item">My Subscription</a>
                        </li>
                    </ul>
                </li>

                <li class="slide">
                    <a href="{{ route('billing.index') }}" class="side-menu__item">
                        <i class="bx bx-wallet side-menu__icon"></i>
                        <span class="side-menu__label">Billing History</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('billing.invoices.index') }}" class="side-menu__item">
                        <i class="bx bx-receipt side-menu__icon"></i>
                        <span class="side-menu__label">Invoices</span>
                    </a>
                </li>

                <!-- ================= SETTINGS ================= -->
                <li class="slide__category">
                    <span class="category-name">Settings</span>
                </li>

                <li class="slide">
                    <a href="{{ route('admin.subscriptions.index') }}" class="side-menu__item">
                        <i class="bx bx-cog side-menu__icon"></i>
                        <span class="side-menu__label">Admin: Subscriptions</span>
                    </a>
                </li>

                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-cog side-menu__icon"></i>
                        <span class="side-menu__label">Settings</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide">
                            <a href="javascript:void(0);" class="side-menu__item">WhatsApp Integration</a>
                        </li>
                        <li class="slide">
                            <a href="javascript:void(0);" class="side-menu__item">Twilio Integration</a>
                        </li>
                    </ul>
                </li>

            </ul>


            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg></div>
        </nav>
        <!-- End::nav -->

    </div>
    <!-- End::main-sidebar -->

</aside>
