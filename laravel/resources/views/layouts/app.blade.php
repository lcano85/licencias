<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @php($pageTitle = trim($__env->yieldContent('title')))
        @if($pageTitle !== '')
            {{ __($pageTitle) }} - {{ config('app.name') }}
        @else
            {{ config('app.name', __('Laravel')) }}
        @endif
    </title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="shortcut icon" href="{{ asset('admin/images/favicon.ico') }}" />

    <link href="{{ asset('admin/css/vendor.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/app.min.css') }}" rel="stylesheet">

    @yield('styles')
    <script src="{{ asset('admin/js/config.js') }}"></script>
</head>

<body>
<div class="wrapper">

    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="page-content">
        <div class="container-fluid">
            @yield('content')
        </div>
        @include('layouts.footer')
    </div>
</div>

<script src="{{ asset('admin/js/vendor.js') }}"></script>
<script src="{{ asset('admin/js/app.js') }}"></script>

<script src="{{ asset('admin/js/jsvectormap/jsvectormap.min.js') }}"></script>
<script src="{{ asset('admin/js/jsvectormap/maps/world-merc.js') }}"></script>
<script src="{{ asset('admin/js/jsvectormap/maps/world.js') }}"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
window.codexTranslations = {!! json_encode(
    (($__codexTranslationFile = resource_path('lang/' . app()->getLocale() . '.json')) && file_exists($__codexTranslationFile))
        ? (json_decode(file_get_contents($__codexTranslationFile), true) ?: [])
        : [],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) !!};

window.codexPermissionTranslations = {!! json_encode(trans('permission'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
window.codexRoleTranslations = {!! json_encode(trans('role'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

window.codexDataTableLanguage = function () {
    return {
        search: "{{ __('Search...') }}",
        zeroRecords: "{{ __('No matching records found') }}",
        info: "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
        infoEmpty: "{{ __('Showing 0 to 0 of 0 entries') }}",
        infoFiltered: "{{ __('(filtered from _MAX_ total entries)') }}",
        lengthMenu: "{{ __('Show _MENU_ entries') }}",
        loadingRecords: "{{ __('Loading...') }}",
        processing: "{{ __('Processing') }}",
        emptyTable: "{{ __('No data available in table') }}",
        paginate: {
            first: "{{ __('First') }}",
            last: "{{ __('Last') }}",
            next: "{{ __('Next') }}",
            previous: "{{ __('Previous') }}"
        },
        aria: {
            sortAscending: "{{ __('Sort ascending') }}",
            sortDescending: "{{ __('Sort descending') }}"
        }
    };
};

window.codexLocale = "{{ app()->getLocale() }}";
window.codexSystemValueKeys = [
    'Active',
    'Activity',
    'Inactive',
    'In Active',
    'In active',
    'Pending',
    'Completed',
    'Created',
    'Review',
    'Reject',
    'Rejected',
    'Cancel',
    'Canceled',
    'Cancelled',
    'Suspended',
    'Expired',
    'Finished',
    'On time',
    'Delayed',
    'Priority',
    'Admin',
    'Manager',
    'Management',
    'Accountant',
    'Master Admin',
    'No Role',
    'N/A',
    'Monthly',
    'Quarterly',
    'Annual',
    'Invoiced',
    'Discarded',
    'Complete',
    'Portfolio',
    'Awaiting Purchase Order',
    'New Agreement',
    'Meeting',
    'Hearing',
    'Deadline',
    'Schedule',
    'Unauthorized',
    'Create role',
    'Edit role',
    'Delete role',
    'List roles',
    'Create user',
    'Edit user',
    'Delete user',
    'List users',
    'Create permission',
    'Edit permission',
    'Delete permission',
    'List permissions',
    'create-role',
    'edit-role',
    'delete-role',
    'list-role',
    'create-user',
    'edit-user',
    'delete-user',
    'list-user',
    'create-permission',
    'edit-permission',
    'delete-permission',
    'list-permission'
];

window.codexCalendarLocale = {
    code: "{{ app()->getLocale() }}",
    buttonText: {
        today: "{{ __('Today') }}",
        month: "{{ __('Month') }}",
        week: "{{ __('Week') }}",
        day: "{{ __('Day') }}",
        list: "{{ __('List') }}",
    },
    buttonHints: {
        prev: "{{ __('Previous') }}",
        next: "{{ __('Next') }}",
    },
    allDayText: "{{ __('All Day') }}",
    moreLinkText: "{{ __('more') }}",
    noEventsText: "{{ __('No events to display') }}",
};

window.codexNormalizeTranslationKey = function (value) {
    return String(value ?? '')
        .replace(/\u00a0/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
};

window.codexBuildTranslationIndex = function (source) {
    const index = {};

    Object.entries(source || {}).forEach(([key, value]) => {
        index[key] = value;
        index[window.codexNormalizeTranslationKey(key)] = value;
    });

    return index;
};

window.codexTranslationIndex = window.codexBuildTranslationIndex(window.codexTranslations);
window.codexSystemTranslationIndex = window.codexBuildTranslationIndex(
    Array.isArray(window.codexSystemValueKeys)
        ? window.codexSystemValueKeys.reduce((carry, key) => {
            if (Object.prototype.hasOwnProperty.call(window.codexTranslations, key)) {
                carry[key] = window.codexTranslations[key];
            }

            return carry;
        }, {})
        : {}
);

window.codexPermissionTranslationIndex = window.codexBuildTranslationIndex(
    Object.entries(window.codexPermissionTranslations || {}).reduce((carry, [key, value]) => {
        carry[key] = value;
        carry[key.replace(/-/g, ' ')] = value;
        return carry;
    }, {})
);

window.codexRoleTranslationIndex = window.codexBuildTranslationIndex(window.codexRoleTranslations || {});

window.codexTranslateValue = function (value, systemOnly = false) {
    if (typeof value !== 'string') {
        return value;
    }

    const exactValue = value.trim();
    const index = systemOnly ? window.codexSystemTranslationIndex : window.codexTranslationIndex;

    if (Object.prototype.hasOwnProperty.call(index, exactValue)) {
        return index[exactValue];
    }

    const normalizedValue = window.codexNormalizeTranslationKey(exactValue);

    if (Object.prototype.hasOwnProperty.call(index, normalizedValue)) {
        return index[normalizedValue];
    }

    if (Object.prototype.hasOwnProperty.call(window.codexPermissionTranslationIndex, exactValue)) {
        return window.codexPermissionTranslationIndex[exactValue];
    }

    if (Object.prototype.hasOwnProperty.call(window.codexPermissionTranslationIndex, normalizedValue)) {
        return window.codexPermissionTranslationIndex[normalizedValue];
    }

    if (Object.prototype.hasOwnProperty.call(window.codexRoleTranslationIndex, exactValue)) {
        return window.codexRoleTranslationIndex[exactValue];
    }

    if (Object.prototype.hasOwnProperty.call(window.codexRoleTranslationIndex, normalizedValue)) {
        return window.codexRoleTranslationIndex[normalizedValue];
    }

    return value;
};

window.codexApplyDataTableDefaults = function () {
    if (!window.jQuery || !jQuery.fn || !jQuery.fn.dataTable) {
        return false;
    }

    jQuery.extend(true, jQuery.fn.dataTable.defaults, {
        language: window.codexDataTableLanguage(),
        drawCallback: function () {
            window.codexTranslateFragment(document.body);
        }
    });

    return true;
};

window.codexShouldSkipTranslation = function (element) {
    if (!element) return false;
    if (element.closest('[data-no-translate="true"]')) return true;
    
    return element.closest('script, style, code, pre, textarea')
        || element.closest('.logo-box')
        || element.matches('img, .logo-box, .logo-box *, .logo-dark, .logo-light');
};
window.codexTranslateAttributes = function (element, systemOnly = false) {
    ['placeholder', 'title', 'aria-label'].forEach((attribute) => {
        const currentValue = element.getAttribute(attribute);

        if (!currentValue) {
            return;
        }

        const translatedValue = window.codexTranslateValue(currentValue, systemOnly);

        if (translatedValue !== currentValue) {
            element.setAttribute(attribute, translatedValue);
        }
    });
};

window.codexTranslateElementText = function (element, systemOnly = false) {
    if (window.codexShouldSkipTranslation(element)) {
        return;
    }    const directTextNodes = Array.from(element.childNodes || []).filter(function (node) {
        return node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '';
    });

    if (directTextNodes.length > 0) {
        directTextNodes.forEach(function (node) {
            const originalText = node.textContent;
            const translatedText = window.codexTranslateValue(originalText, systemOnly);

            if (translatedText !== originalText) {
                node.textContent = translatedText;
            }
        });

        return;
    }

    const originalText = element.textContent;
    const translatedText = window.codexTranslateValue(originalText, systemOnly);

    if (translatedText !== originalText) {
        element.textContent = translatedText;
    }
};

window.codexTranslateFragment = function (root) {
    if (!root || window.codexLocale === 'en') {
        return;
    }

    const staticSelectors = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'label', 'button', 'small', 'strong', 'th', 'option',
        '.menu-title', '.nav-text', '.dropdown-item', '.card-title',
        '.modal-title', '.offcanvas-title', '.timeline-badge',
        '.swal2-title', '.swal2-confirm', '.swal2-cancel', '.swal2-deny'
    ];
    const systemValueSelectors = [
        'td', '.badge', '.toast-message', '.swal2-html-container p',
        '.swal2-html-container span', '.swal2-html-container strong'
    ];
    const attributeSelectors = [
        'input[placeholder]', 'textarea[placeholder]', '[title]', '[aria-label]'
    ];

    staticSelectors.forEach((selector) => {
        if (root.matches && root.matches(selector)) {
            window.codexTranslateElementText(root);
            window.codexTranslateAttributes(root);
        }

        root.querySelectorAll(selector).forEach((element) => {
            window.codexTranslateElementText(element);
            window.codexTranslateAttributes(element);
        });
    });

    systemValueSelectors.forEach((selector) => {
        if (root.matches && root.matches(selector)) {
            window.codexTranslateElementText(root, true);
            window.codexTranslateAttributes(root, true);
        }

        root.querySelectorAll(selector).forEach((element) => {
            window.codexTranslateElementText(element, true);
            window.codexTranslateAttributes(element, true);
        });
    });

    attributeSelectors.forEach((selector) => {
        if (root.matches && root.matches(selector)) {
            window.codexTranslateAttributes(root);
        }

        root.querySelectorAll(selector).forEach((element) => {
            window.codexTranslateAttributes(element);
        });
    });
};

document.addEventListener('DOMContentLoaded', function () {
    if (!window.codexApplyDataTableDefaults()) {
        let attempts = 0;
        const dataTableDefaultsInterval = setInterval(function () {
            attempts += 1;

            if (window.codexApplyDataTableDefaults() || attempts >= 30) {
                clearInterval(dataTableDefaultsInterval);
            }
        }, 250);
    }

    if (window.codexLocale !== 'en') {
        window.codexTranslateFragment(document.body);

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === 'characterData' && mutation.target.parentElement) {
                    window.codexTranslateFragment(mutation.target.parentElement);
                }

                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        window.codexTranslateFragment(node);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true, characterData: true });
    }

    if (window.jQuery) {
        jQuery(document).on('draw.dt', function () {
            window.codexTranslateFragment(document.body);
        });
    }

    if (window.toastr && !window.toastr.__codexWrapped) {
        ['success', 'error', 'warning', 'info'].forEach(function (method) {
            const originalMethod = window.toastr[method].bind(window.toastr);

            window.toastr[method] = function (message, title, optionsOverride) {
                return originalMethod(
                    window.codexTranslateValue(message),
                    window.codexTranslateValue(title),
                    optionsOverride
                );
            };
        });

        window.toastr.__codexWrapped = true;
    }

    if (window.Swal && !window.Swal.__codexWrapped) {
        const originalFire = window.Swal.fire.bind(window.Swal);

        window.Swal.fire = function (...args) {
            if (typeof args[0] === 'string') {
                args[0] = window.codexTranslateValue(args[0]);

                if (typeof args[1] === 'string') {
                    args[1] = window.codexTranslateValue(args[1]);
                }
            } else if (args[0] && typeof args[0] === 'object') {
                ['title', 'text', 'confirmButtonText', 'cancelButtonText', 'denyButtonText', 'inputLabel', 'inputPlaceholder', 'footer'].forEach(function (key) {
                    if (typeof args[0][key] === 'string') {
                        args[0][key] = window.codexTranslateValue(args[0][key]);
                    }
                });
            }

            const result = originalFire(...args);

            setTimeout(function () {
                window.codexTranslateFragment(document.body);
            }, 0);

            return result;
        };

        window.Swal.__codexWrapped = true;
    }
});
</script>

{{-- Toastr --}}
<script>
@if(Session::has('success'))
toastr.success("{{ __(Session::get('success')) }}");
@endif
@if(Session::has('error'))
toastr.error("{{ __(Session::get('error')) }}");
@endif
@if(Session::has('warning'))
toastr.warning("{{ __(Session::get('warning')) }}");
@endif
@if(Session::has('info'))
toastr.info("{{ __(Session::get('info')) }}");
@endif
</script>

@yield('script')
</body>
</html>
