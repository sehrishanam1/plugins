jQuery(function ($) {

  var currentSection = '';

  // ── Navigation ─────────────────────────────────────────────────────
  function goTo(section) {
    currentSection = section;
    $('.ap-nav-item').removeClass('active');
    $('.ap-nav-item[data-section="' + section + '"]').addClass('active');
    $('.ap-section').hide();
    $('.ap-section[data-section="' + section + '"]').show();
    localStorage.setItem('ap_section', section);
  }

  $('.ap-nav-item').on('click', function (e) {
    e.preventDefault();
    goTo($(this).data('section'));
  });

  // Restore last section
  var saved = localStorage.getItem('ap_section');
  var first = $('.ap-nav-item').first().data('section');
  goTo(saved || first);

  // ── Toast ───────────────────────────────────────────────────────────
  function toast(msg, type) {
    var $t = $('#ap-toast');
    $t.text(msg).removeClass('show-ok show-err').addClass(type === 'ok' ? 'show-ok' : 'show-err');
    clearTimeout(window._apToastTimer);
    window._apToastTimer = setTimeout(function () { $t.removeClass('show-ok show-err'); }, 3000);
  }

  // ── Save ────────────────────────────────────────────────────────────
  $('#btn-save').on('click', function () {
    var $btn = $(this).prop('disabled', true).text('Saving…');
    var payload = $('#ap-form').serialize();

    $.post(AP.ajax, {
      action:  'ap_save',
      nonce:   AP.nonce,
      section: currentSection,
      payload: payload,
    })
    .done(function (r) {
      if (r.success) toast('✓ Saved at ' + r.data.time, 'ok');
      else toast('✗ Save failed', 'err');
    })
    .fail(function () { toast('✗ Network error', 'err'); })
    .always(function () { $btn.prop('disabled', false).text('Save Changes'); });
  });

  // ── Reset Section ───────────────────────────────────────────────────
  $('#btn-reset-section').on('click', function () {
    if (!confirm('Reset all fields in the "' + currentSection.replace('_', ' ') + '" section to defaults?')) return;
    $.post(AP.ajax, {
      action:  'ap_reset_section',
      nonce:   AP.nonce,
      section: currentSection,
    })
    .done(function (r) {
      if (r.success) { toast('✓ Section reset — reloading…', 'ok'); setTimeout(function () { location.reload(); }, 900); }
      else toast('✗ Reset failed', 'err');
    });
  });

  // ── Reset All ───────────────────────────────────────────────────────
  $('#btn-reset-all').on('click', function () {
    if (!confirm('Reset ALL ArionPlay options to defaults? This cannot be undone.')) return;
    $.post(AP.ajax, {
      action: 'ap_reset_all',
      nonce:  AP.nonce,
    })
    .done(function (r) {
      if (r.success) { toast('✓ All options reset — reloading…', 'ok'); setTimeout(function () { location.reload(); }, 900); }
      else toast('✗ Reset failed', 'err');
    });
  });

  // ── Media Library ───────────────────────────────────────────────────
  $(document).on('click', '.ap-upload-btn', function () {
    var targetId = $(this).data('target');
    var $field   = $(this).closest('.ap-field-image');

    var frame = wp.media({ title: 'Select Image', button: { text: 'Use this image' }, multiple: false });
    frame.on('select', function () {
      var url = frame.state().get('selection').first().toJSON().url;
      $('#' + targetId).val(url);
      updatePreview($field, url);
      // Show remove button if hidden
      if (!$field.find('.ap-remove-btn').length) {
        $field.find('.ap-upload-btn').after('<button type="button" class="ap-remove-btn" data-target="' + targetId + '">Remove</button>');
      }
    });
    frame.open();
  });

  $(document).on('click', '.ap-remove-btn', function () {
    var targetId = $(this).data('target');
    var $field   = $(this).closest('.ap-field-image');
    $('#' + targetId).val('');
    updatePreview($field, '');
    $(this).remove();
  });

  $(document).on('input', '.ap-img-url', function () {
    updatePreview($(this).closest('.ap-field-image'), $(this).val());
  });

  function updatePreview($field, url) {
    var $wrap = $field.find('.ap-img-preview-wrap');
    if (url) {
      var $img = $wrap.find('.ap-img-preview');
      if ($img.length) $img.attr('src', url);
      else $wrap.html('<img src="' + url + '" class="ap-img-preview" alt="">');
    } else {
      $wrap.empty();
    }
  }

  // ── Ctrl/Cmd + S ────────────────────────────────────────────────────
  $(document).on('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      $('#btn-save').trigger('click');
    }
  });

});
