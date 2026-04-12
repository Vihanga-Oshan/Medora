(function () {
  function initProfileMenus() {
    var profileBlocks = document.querySelectorAll('.js-admin-profile');
    if (!profileBlocks.length) return;

    function closeBlock(block) {
      block.classList.remove('is-open');
      var t = block.querySelector('.admin-profile-trigger');
      if (t) t.setAttribute('aria-expanded', 'false');
      var menu = block.querySelector('.admin-profile-menu');
      if (menu) menu.hidden = true;
    }

    function openBlock(block) {
      block.classList.add('is-open');
      var t = block.querySelector('.admin-profile-trigger');
      if (t) t.setAttribute('aria-expanded', 'true');
      var menu = block.querySelector('.admin-profile-menu');
      if (menu) menu.hidden = false;
    }

    profileBlocks.forEach(function (block) {
      var trigger = block.querySelector('.admin-profile-trigger');
      if (!trigger) return;
      closeBlock(block);

      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var opening = !block.classList.contains('is-open');
        profileBlocks.forEach(function (b) {
          closeBlock(b);
        });

        if (opening) {
          openBlock(block);
        }
      });
    });

    document.addEventListener('click', function (e) {
      profileBlocks.forEach(function (block) {
        if (!block.contains(e.target)) {
          closeBlock(block);
        }
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        profileBlocks.forEach(function (block) {
          closeBlock(block);
        });
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProfileMenus);
  } else {
    initProfileMenus();
  }
})();
