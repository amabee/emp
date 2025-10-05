<?php
$page_title = 'My Profile';
include './shared/session_handler.php';
if (!isset($user_id)) {
  header('Location: ./login.php');
  exit();
}
ob_start();
?>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="row gx-4 align-items-center">
          <div class="col-md-4 text-center border-end">
            <div id="avatarCircle" class="mx-auto mb-3" style="width:120px;height:120px;border-radius:50%;background:#f1f3f5;display:flex;align-items:center;justify-content:center;font-size:36px;color:#495057;font-weight:600;">
              <!-- initials set by JS -->
            </div>
            <div class="mb-3">
              <button id="changePasswordToggle" type="button" class="btn btn-outline-secondary btn-sm">Change password</button>
            </div>
            <p class="text-muted small">Tip: change your username or email and hit Save Profile.</p>
          </div>

          <div class="col-md-8">
            <h5 class="mb-3">Profile</h5>
            <form id="profileForm">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Username</label>
                  <input class="form-control" name="username" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email</label>
                  <input class="form-control" name="email" type="email">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">First name</label>
                  <input class="form-control" name="first_name">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Last name</label>
                  <input class="form-control" name="last_name">
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">Save Profile</button>
                <button type="button" class="btn btn-outline-secondary" id="cancelProfile">Reset</button>
              </div>
            </form>

            <div id="changePasswordCard" class="mt-4" style="display:none;">
              <h6>Change password</h6>
              <form id="changePasswordForm">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">New Password</label>
                    <input class="form-control" name="new_password" type="password" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input class="form-control" name="confirm_password" type="password" required>
                  </div>
                </div>
                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn btn-danger">Change Password</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function waitForJQ() { if (typeof $ === 'undefined') return setTimeout(waitForJQ,50); initProfile(); })();

  function initProfile() {
    $(function() {
      loadProfile();

      $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type=submit]');
        const orig = $btn.html(); $btn.prop('disabled',true).html('Saving...');
        $.post('../ajax/update_user_profile.php', $(this).serialize(), function(res) {
          $btn.prop('disabled',false).html(orig);
          if (res && res.success) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: res.message || 'Saved' });
            else alert(res.message || 'Saved');
            loadProfile();
          } else {
            if (typeof Swal !== 'undefined') Swal.fire('Error', res.message || 'Failed', 'error');
            else alert(res.message || 'Failed');
          }
        }, 'json').fail(function() { $btn.prop('disabled',false).html(orig); if (typeof Swal !== 'undefined') Swal.fire('Error', 'Server error', 'error'); else alert('Server error'); });
      });

      $('#cancelProfile').on('click', function() { loadProfile(); });

      $('#changePasswordToggle').on('click', function() { $('#changePasswordCard').toggle(); $('html,body').animate({scrollTop: $('#changePasswordCard').offset().top - 100}, 300); });

      $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type=submit]');
        const orig = $btn.html(); $btn.prop('disabled',true).html('Changing...');
        const data = $(this).serialize() + '&action=password';
        $.post('../ajax/update_user_profile.php', data, function(res) {
          $btn.prop('disabled',false).html(orig);
          if (res && res.success) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: res.message || 'Password changed' });
            else alert(res.message || 'Password changed');
            $('#changePasswordForm')[0].reset();
            $('#changePasswordCard').hide();
          } else {
            if (typeof Swal !== 'undefined') Swal.fire('Error', res.message || 'Failed', 'error');
            else alert(res.message || 'Failed');
          }
        }, 'json').fail(function() { $btn.prop('disabled',false).html(orig); if (typeof Swal !== 'undefined') Swal.fire('Error', 'Server error', 'error'); else alert('Server error'); });
      });
    });
  }

  function loadProfile() {
    $.get('../ajax/get_user_profile.php', function(res) {
      if (!res || !res.success) return;
      const u = res.user || {};
      $('input[name="username"]').val(u.username || '');
      $('input[name="first_name"]').val(u.first_name || '');
      $('input[name="last_name"]').val(u.last_name || '');
      $('input[name="email"]').val(u.email || '');
      // set avatar image or initials
      const $avatar = $('#avatarCircle');
      $avatar.empty();
      if (u.image) {
        // create img element
        const img = document.createElement('img');
        img.src = u.image;
        img.alt = (u.first_name || u.username || 'User') + ' avatar';
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '50%';
        $avatar.append(img);
      } else {
        const initials = ((u.first_name||'').charAt(0) + (u.last_name||'').charAt(0)).toUpperCase() || (u.username||'').charAt(0).toUpperCase() || '?';
        $avatar.text(initials);
      }
    }, 'json').fail(function() { if (typeof Swal !== 'undefined') Swal.fire('Error', 'Failed to load profile', 'error'); else alert('Failed to load profile'); });
  }
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>
