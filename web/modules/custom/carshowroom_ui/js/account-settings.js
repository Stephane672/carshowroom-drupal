(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.csAccountForm = {
    attach: function (context) {
      once('cs-account-form', '.cs-account-form', context).forEach(function (form) {
        var passFields = form.querySelector('.js-cs-password-fields');
        var passDisplay = form.querySelector('.cs-password-display');
        var passChange = form.querySelector('.js-cs-password-change');
        var emailChange = form.querySelector('.js-cs-email-change');
        var mailInput = form.querySelector('input[name="mail"]');

        function revealPasswordFields() {
          if (passFields) {
            passFields.classList.add('is-open');
          }
          if (passDisplay) {
            passDisplay.classList.add('is-hidden');
          }
        }

        if (passChange) {
          passChange.addEventListener('click', function (e) {
            e.preventDefault();
            revealPasswordFields();
            var first = passFields && passFields.querySelector('input');
            if (first) {
              first.focus();
            }
          });
        }

        if (emailChange) {
          emailChange.addEventListener('click', function (e) {
            e.preventDefault();
            if (mailInput) {
              mailInput.readOnly = false;
              mailInput.classList.remove('cs-input-readonly');
              mailInput.focus();
            }
            revealPasswordFields();
            emailChange.classList.add('is-hidden');
          });
        }
      });

      once('cs-avatar-preview', '.cs-avatar-side input[type="file"]', context).forEach(function (input) {
        input.addEventListener('change', function () {
          if (!input.files || !input.files[0]) {
            return;
          }
          var circle = input.closest('.cs-avatar-side').querySelector('.cs-avatar-circle');
          if (!circle) {
            return;
          }
          var reader = new FileReader();
          reader.onload = function (e) {
            var img = circle.querySelector('img.cs-avatar-live-preview');
            if (!img) {
              img = document.createElement('img');
              img.className = 'cs-avatar-live-preview';
              circle.appendChild(img);
            }
            img.src = e.target.result;
          };
          reader.readAsDataURL(input.files[0]);
        });
      });
    }
  };
})(Drupal, once);
