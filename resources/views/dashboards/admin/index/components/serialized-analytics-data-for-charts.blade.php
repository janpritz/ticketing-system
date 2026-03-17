<div id="analytics-data" class="hidden" data-week-labels='@json($weekLabels ?? [])'
    data-week-data='@json($weekData ?? [])' data-category-labels='@json($categoryLabels ?? [])'
    data-category-data='@json($categoryData ?? [])' data-active-staff='@json($activeStaff ?? [])'
    data-staff-contacts='@json($staffContacts ?? [])' data-admin-url="{{ route('admin.dashboard.data') }}"></div>
<div id="faq-updater-data" class="hidden" data-secret="{{ $faqUpdaterSecret ?? '' }}"
    data-url="{{ $faqUpdaterUrl ?? '' }}"></div>