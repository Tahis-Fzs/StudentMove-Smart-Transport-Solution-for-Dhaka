@php
    $uniValues = $universities->flatMap(fn ($u) => array_filter([$u->name, $u->short_name]))->unique()->values();
    $deptValues = $departments->values();
@endphp
<script>
(function () {
    const audience = document.getElementById('audienceSelect');
    const group = document.getElementById('targetValueGroup');
    const input = document.getElementById('targetValueInput');
    const hint = document.getElementById('targetHint');
    const list = document.getElementById('targetSuggestions');
    if (!audience || !group || !input) return;

    const suggestions = {
        university: @json($uniValues),
        department: @json($deptValues),
        route: ['Uttara', 'DSC', 'Mirpur', 'Gulshan', 'DU', 'BUET'],
        all: []
    };
    const hints = {
        all: 'Broadcast to every signed-in student.',
        university: 'Match profile university (name or short code, e.g. DIU).',
        department: 'Match profile department (e.g. CSE).',
        route: 'Match students who saved a route containing this text.'
    };

    function sync() {
        const mode = audience.value;
        group.style.display = mode === 'all' ? 'none' : '';
        input.required = mode !== 'all';
        if (hint) hint.textContent = hints[mode] || '';
        if (list) {
            list.innerHTML = '';
            (suggestions[mode] || []).forEach(function (v) {
                const opt = document.createElement('option');
                opt.value = v;
                list.appendChild(opt);
            });
        }
    }

    audience.addEventListener('change', sync);
    sync();
})();
</script>
