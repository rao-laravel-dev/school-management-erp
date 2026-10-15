<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div><img src="{{ asset('backend/assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon"></div>
        <div>
            <h4 class="logo-text">Accountant</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i></div>
    </div>

    <ul class="metismenu" id="menu">
        <li>
            <a href="{{ route('accountant.dashboard') }}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        {{-- Fees --}}
        @canany(['access-fee-collections', 'manage-fee-collections', 'access-fee-structures', 'access-fee-types', 'access-discount-policies', 'access-fine-policies'])
        @php $open = request()->routeIs('fees.*', 'fee_structure.*', 'fee_types.*', 'discount_policies.*', 'fine_policies.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-money"></i></div>
                <div class="menu-title">Fees</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-fee-collections')
                <li><a href="{{ route('fees.collect.index') }}"><i class='bx bx-radio-circle'></i>Collect Fees</a></li>
                @endcan
                @can('manage-fee-collections')
                <li><a href="{{ route('fees.collect.report') }}"><i class='bx bx-radio-circle'></i>Fee Collection Report</a></li>
                @endcan
                @can('access-fee-structures')
                <li><a href="{{ route('fee_structure.index') }}"><i class='bx bx-radio-circle'></i>Fee Structure</a></li>
                @endcan
                @can('access-fee-types')
                <li><a href="{{ route('fee_types.index') }}"><i class='bx bx-radio-circle'></i>Fee Types</a></li>
                @endcan
                @can('access-discount-policies')
                <li><a href="{{ route('discount_policies.index') }}"><i class='bx bx-radio-circle'></i>Discount Policies</a></li>
                @endcan
                @can('access-fine-policies')
                <li><a href="{{ route('fine_policies.index') }}"><i class='bx bx-radio-circle'></i>Fine Policies</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- Accounts --}}
        @canany(['access-bank-accounts', 'access-expense-categories', 'access-expenses'])
        @php $open = request()->routeIs('bank_accounts.*', 'expense_categories.*', 'expenses.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-wallet"></i></div>
                <div class="menu-title">Accounts</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-bank-accounts')
                <li><a href="{{ route('bank_accounts.index') }}"><i class='bx bx-radio-circle'></i>Bank Accounts</a></li>
                @endcan
                @can('access-expense-categories')
                <li><a href="{{ route('expense_categories.index') }}"><i class='bx bx-radio-circle'></i>Expense Categories</a></li>
                @endcan
                @can('access-expenses')
                <li><a href="{{ route('expenses.index') }}"><i class='bx bx-radio-circle'></i>Expenses</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- Payroll --}}
        @canany(['access-salary-slips', 'manage-salary-slips', 'access-staff-attendance'])
        @php $open = request()->routeIs('salary_slips.*', 'staff_attendance.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-receipt"></i></div>
                <div class="menu-title">Payroll</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-salary-slips')
                <li><a href="{{ route('salary_slips.index') }}"><i class='bx bx-radio-circle'></i>Salary Slips</a></li>
                @endcan
                @can('manage-salary-slips')
                <li><a href="{{ route('salary_slips.create') }}"><i class='bx bx-radio-circle'></i>Generate Salary</a></li>
                @endcan
                @can('access-staff-attendance')
                <li><a href="{{ route('staff_attendance.report') }}"><i class='bx bx-radio-circle'></i>Staff Attendance Report</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- People: Receptionist / Teacher Management / Student Information --}}
        @canany(['access-receptionist', 'access-teacher', 'access-students'])
        @php $open = request()->routeIs('receptionist.*', 'teacher.*', 'students.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-group"></i></div>
                <div class="menu-title">People</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-receptionist')
                <li><a href="{{ route('receptionist.index') }}"><i class='bx bx-radio-circle'></i>Receptionist List</a></li>
                @endcan
                @can('access-teacher')
                <li><a href="{{ route('teacher.index') }}"><i class='bx bx-radio-circle'></i>Teachers List</a></li>
                @endcan
                @can('access-students')
                <li><a href="{{ route('students.index') }}"><i class='bx bx-radio-circle'></i>Students List</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        @can('access-leave-application')
        @php $open = request()->routeIs('staffleaveapp.create'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-calendar-x'></i></div>
                <div class="menu-title">Leave Applications</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                {{-- create route index page par redirect karta hai (apply modal wahin hai) --}}
                <li><a href="{{ route('staffleaveapp.create') }}"><i class='bx bx-radio-circle'></i>Apply Leave</a></li>
            </ul>
        </li>
        @endcan

        {{-- Reports: logged-in user ki apni reports + finance/attendance reports --}}
        @canany(['access-my-attendance', 'access-leave-application', 'access-my-salary-slips', 'access-finance-report', 'access-attendance'])
        @php $open = request()->routeIs('my.*', 'staffleaveapp.*', 'finance_report.*', 'attendance.*') && ! request()->routeIs('staffleaveapp.create'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-file'></i></div>
                <div class="menu-title">Reports</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-finance-report')
                <li><a href="{{ route('finance_report.index') }}"><i class='bx bx-radio-circle'></i>Finance Report</a></li>
                @endcan
                @can('access-attendance')
                <li><a href="{{ route('attendance.index') }}"><i class='bx bx-radio-circle'></i>Class Attendance Report</a></li>
                @endcan
                @can('access-my-attendance')
                <li><a href="{{ route('my.attendance.index') }}"><i class='bx bx-radio-circle'></i>My Attendance Report</a></li>
                @endcan
                @can('access-leave-application')
                <li><a href="{{ route('staffleaveapp.index') }}"><i class='bx bx-radio-circle'></i>My Leave Report</a></li>
                @endcan
                @can('access-my-salary-slips')
                <li><a href="{{ route('my.salary_slips.index') }}"><i class='bx bx-radio-circle'></i>My Salary Slip</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- Logout (hamesha last) --}}
        <li>
            <a href="{{ route('accountant.logout') }}">
                <div class="parent-icon"><i class="bx bx-log-out-circle"></i></div>
                <div class="menu-title">Logout</div>
            </a>
        </li>
    </ul>
</div>
