// assets/js/auth.js
$(function(){
  const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
  // init bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })
  function showAlert(msg, type='success'){
    // show inline alert
    $('#auth-alert').html(`<div class="alert alert-${type}" role="alert">${msg}</div>`);
    // show toast
    const toastEl = document.getElementById('auth-toast');
    if(toastEl){
      const bsToast = new bootstrap.Toast(toastEl);
      document.getElementById('toast-title').textContent = type === 'success' ? 'Success' : 'Error';
      document.getElementById('toast-body').textContent = msg;
      bsToast.show();
    }
  }

  function setLoading($form, loading){
    const $btn = $form.find('button[type=submit]');
    if(loading){
      $btn.prop('disabled', true);
      if(!$btn.find('.spinner-border').length){
        $btn.data('orig-text', $btn.html());
        $btn.prepend(' <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ');
      }
    } else {
      $btn.prop('disabled', false);
      if($btn.data('orig-text')){
        $btn.html($btn.data('orig-text'));
        $btn.removeData('orig-text');
      }
    }
  }

  function ajaxForm($form, data){
    setLoading($form, true);
    // ensure CSRF token is sent in the POST body as a fallback if headers are stripped
    data = data || {};
    if(csrfToken && !data.csrf_token) data.csrf_token = csrfToken;
    return $.ajax({
      url: '/api/auth.php',
      method: 'POST',
      data: data,
      dataType: 'json',
      beforeSend: function(xhr){ if(csrfToken) xhr.setRequestHeader('X-CSRF-Token', csrfToken); }
    }).always(()=> setLoading($form, false));
  }

  // unified fail handler to show server-provided error messages when available
  function handleAjaxFail(jqXHR, textStatus, errorThrown){
    let msg = 'Server error';
    try{
      if(jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.message) msg = jqXHR.responseJSON.message;
      else if(jqXHR && jqXHR.responseText) {
        // try to parse JSON from text
        try{ const parsed = JSON.parse(jqXHR.responseText); if(parsed && parsed.message) msg = parsed.message; }
        catch(e){ msg = jqXHR.responseText.substring(0,200); }
      }
    }catch(e){ /* ignore */ }
    showAlert(msg,'danger');
  }

  // basic client-side validators
  function validEmail(email){
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  // Password strength estimator (simple rules)
  function estimatePassword(password){
    const suggestions = [];
    let score = 0;
    if(password.length >= 8){ score += 1; } else { suggestions.push('Use at least 8 characters'); }
    if(/[A-Z]/.test(password)) score += 1; else suggestions.push('Add an uppercase letter');
    if(/[a-z]/.test(password)) score += 1; else suggestions.push('Add a lowercase letter');
    if(/[0-9]/.test(password)) score += 1; else suggestions.push('Include a number');
    if(/[^A-Za-z0-9]/.test(password)) score += 1; else suggestions.push('Include a special character');
    // score 0..5
    return {score, suggestions};
  }

  function updatePasswordUI($pw){
    const val = $pw.val() || '';
    const $bar = $('#pw-strength-bar');
    const $text = $('#pw-strength-text');
    const $list = $('#pw-suggestions');
    if(val.length === 0){ $bar.css('width','0%').removeClass().addClass('progress-bar'); $text.text(''); $list.empty(); return; }
    const {score, suggestions} = estimatePassword(val);
    const pct = Math.round((score/5)*100);
    $bar.css('width', pct + '%');
    $bar.removeClass('bg-success bg-warning bg-danger');
    if(pct >= 80) $bar.addClass('bg-success');
    else if(pct >= 50) $bar.addClass('bg-warning');
    else $bar.addClass('bg-danger');
    const strength = score <= 1 ? 'Very weak' : score === 2 ? 'Weak' : score === 3 ? 'Moderate' : score === 4 ? 'Strong' : 'Very strong';
    $text.text(strength + (suggestions.length ? ' — suggestions below' : ''));
    $list.empty();
    suggestions.forEach(s => $list.append(`<li>• ${s}</li>`));
  }

  $('#signup-form').on('submit', function(e){
    e.preventDefault();
    const $form = $(this);
    const username = $('#username').val().trim();
    const email = $('#email').val().trim();
    const password = $('#password').val();
  // inline field errors
  let fieldErr = false;
  if(!username){ $('#username').addClass('is-invalid'); fieldErr = true; } else { $('#username').removeClass('is-invalid'); }
  if(!validEmail(email)){ $('#email').addClass('is-invalid'); fieldErr = true; } else { $('#email').removeClass('is-invalid'); }
  if(password.length < 8){ $('#password').addClass('is-invalid'); fieldErr = true; } else { $('#password').removeClass('is-invalid'); }
  if(fieldErr) return showAlert('Please correct the highlighted fields','danger');

    ajaxForm($form, {action:'signup', username, email, password}).done(function(resp){
      if(resp.success){ showAlert(resp.message,'success'); setTimeout(()=> location.href='/auth/login.php',1000); }
      else showAlert(resp.message,'danger');
  }).fail(handleAjaxFail);
  });

  $('#login-form').on('submit', function(e){
    e.preventDefault();
    const $form = $(this);
    const email = $('#email').val().trim();
    const password = $('#password').val();
  if(!email || !password){ if(!email) $('#email').addClass('is-invalid'); if(!password) $('#password').addClass('is-invalid'); return showAlert('Email and password are required','danger'); }

  const remember = $('#remember').is(':checked') ? '1' : '0';
  // persist user's preference
  try{ localStorage.setItem('remember_pref', remember); }catch(e){}
  ajaxForm($form, {action:'login', email, password, remember}).done(function(resp){
      if(resp.success){ showAlert(resp.message,'success'); setTimeout(()=> location.href='/',500); }
      else showAlert(resp.message,'danger');
  }).fail(handleAjaxFail);
  });

  $('#request-reset-form').on('submit', function(e){
    e.preventDefault();
    const $form = $(this);
    const email = $('#email').val().trim();
  if(!validEmail(email)){ $('#email').addClass('is-invalid'); return showAlert('Enter a valid email','danger'); } else { $('#email').removeClass('is-invalid'); }

    ajaxForm($form, {action:'request_reset', email}).done(function(resp){
      showAlert(resp.message, resp.success ? 'success' : 'danger');
  }).fail(handleAjaxFail);
  });

  $('#reset-form').on('submit', function(e){
    e.preventDefault();
    const $form = $(this);
    const token = $('#token').val();
    const password = $('#password').val();
  if(!token) return showAlert('Invalid token','danger');
  if(password.length < 8){ $('#password').addClass('is-invalid'); return showAlert('Password must be at least 8 characters','danger'); } else { $('#password').removeClass('is-invalid'); }

    ajaxForm($form, {action:'reset_password', token, password}).done(function(resp){
      if(resp.success){ showAlert(resp.message,'success'); setTimeout(()=> location.href='/auth/login.php',800); }
      else showAlert(resp.message,'danger');
  }).fail(handleAjaxFail);
  });

  // wire up password strength listeners for any password inputs
  $(document).on('input', '#password', function(){ updatePasswordUI($(this)); });

  // restore remember checkbox from localStorage if available
  try{
    const pref = localStorage.getItem('remember_pref');
    if(pref !== null){ $('#remember').prop('checked', pref === '1'); }
  }catch(e){}
});
