{{--
    Quill-based rich text editor, used in place of a plain textarea for
    job_posting_description. Can be included more than once per page (e.g.
    a create modal plus one edit modal per job posting) — $uid namespaces
    each instance's DOM ids, and @once keeps the Quill CDN assets from being
    loaded more than once regardless of how many instances are on the page.

    Params: $uid (string, unique per instance), $fieldName (form field name
    the hidden textarea submits as), $initialValue (existing HTML, for edit).
--}}
@php
    $uid = $uid ?? 'new';
    $fieldName = $fieldName ?? 'job_posting_description';
    $initialValue = $initialValue ?? '';
@endphp

@once
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
@endonce

<div>
    <div id="quillToolbar-{{ $uid }}" class="bg-white rounded-t-lg border border-b-0 border-[#0E0F3B]">
        <span class="ql-formats">
            <select class="ql-header">
                <option value="1"></option>
                <option value="2"></option>
                <option value="3"></option>
                <option selected></option>
            </select>
        </span>
        <span class="ql-formats">
            <button class="ql-bold"></button>
            <button class="ql-italic"></button>
            <button class="ql-underline"></button>
            <button class="ql-strike"></button>
        </span>
        <span class="ql-formats">
            <select class="ql-color"></select>
            <select class="ql-background"></select>
        </span>
        <span class="ql-formats">
            <button class="ql-list" value="ordered"></button>
            <button class="ql-list" value="bullet"></button>
        </span>
        <span class="ql-formats">
            <button class="ql-link"></button>
            <button class="ql-image"></button>
            <button class="ql-blockquote"></button>
        </span>
        <span class="ql-formats">
            <button class="ql-clean"></button>
        </span>
    </div>
    <div id="quillEditor-{{ $uid }}" class="bg-white rounded-b-lg border border-[#0E0F3B] text-sm" style="min-height: 150px;"></div>
    <textarea name="{{ $fieldName }}" id="quillHidden-{{ $uid }}" class="hidden">{{ $initialValue }}</textarea>
</div>

<script>
(function () {
    'use strict';

    function init() {
        var uid = @json($uid);
        var hidden = document.getElementById('quillHidden-' + uid);

        var quill = new Quill('#quillEditor-' + uid, {
            theme: 'snow',
            modules: { toolbar: '#quillToolbar-' + uid },
        });

        // Seed with existing content (edit case) — set via API rather than
        // innerHTML so Quill's internal state matches what's on screen.
        if (hidden.value.trim() !== '') {
            quill.clipboard.dangerouslyPasteHTML(hidden.value);
        }

        quill.on('text-change', function () {
            hidden.value = quill.root.innerHTML;
        });

        // Uploads to the server and inserts the returned URL, rather than
        // Quill's default of embedding the file as base64 — keeps the
        // stored description small and matches this app's usual
        // store-then-reference file convention.
        var enclosingForm = document.getElementById('quillEditor-' + uid).closest('form');

        quill.getModule('toolbar').addHandler('image', function () {
            var fileInput = document.createElement('input');
            fileInput.setAttribute('type', 'file');
            fileInput.setAttribute('accept', 'image/*');
            fileInput.click();

            fileInput.onchange = function () {
                var file = fileInput.files[0];
                if (!file) return;

                var formData = new FormData();
                formData.append('image', file);
                // Read the token from this form's own @csrf field rather
                // than assuming a <meta name="csrf-token"> tag exists on
                // every page that uses this editor.
                var tokenInput = enclosingForm ? enclosingForm.querySelector('input[name="_token"]') : null;
                formData.append('_token', tokenInput ? tokenInput.value : '');

                fetch(@json(route('jobPosting.uploadDescriptionImage')), {
                    method: 'POST',
                    body: formData,
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        var range = quill.getSelection(true);
                        quill.insertEmbed(range.index, 'image', data.url, 'user');
                    })
                    .catch(function () { alert('Could not upload image. Please try again.'); });
            };
        });

        // Belt-and-suspenders: sync right before submit too, in case the
        // very last keystroke didn't get a text-change event yet.
        var form = document.getElementById('quillEditor-' + uid).closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                hidden.value = quill.root.innerHTML;
            });
        }
    }

    // This editor usually lives inside a modal that starts out
    // display:none (create/edit job dialogs). Quill measures its container
    // to position the caret and bind click-to-focus handling — if it's
    // constructed while hidden, that measurement comes back zero and
    // clicking straight into the editor silently does nothing until some
    // other control (e.g. the header dropdown) forces a layout recalc. So
    // wait until the container is actually visible before constructing it.
    function whenVisible(callback) {
        var container = document.getElementById('quillEditor-' + @json($uid));
        if (container.offsetParent !== null) {
            callback();
            return;
        }
        var observer = new MutationObserver(function () {
            if (container.offsetParent !== null) {
                observer.disconnect();
                callback();
            }
        });
        observer.observe(document.body, { attributes: true, attributeFilter: ['class', 'style'], subtree: true });
    }

    // The CDN <script> above (loaded once via the @@once directive) is a
    // normal blocking tag (same convention as this app's other CDN
    // includes), so Quill is already defined by the time this runs — but
    // guard anyway in case this partial ever ends up after an
    // async-loaded copy.
    if (window.Quill) {
        whenVisible(init);
    } else {
        window.addEventListener('load', function () { whenVisible(init); });
    }
})();
</script>
