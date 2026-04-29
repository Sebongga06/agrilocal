
    document.addEventListener('DOMContentLoaded', function () {
      const tabs = document.querySelectorAll('.profile-tab');
      const reviewsTab = document.getElementById('reviewsTab');
      const mediaTab = document.getElementById('mediaTab');

      tabs.forEach(tab => {
        tab.addEventListener('click', function () {
          tabs.forEach(t => t.classList.remove('active'));
          this.classList.add('active');

          const target = this.getAttribute('data-tab');

          if (target === 'reviewsTab') {
            reviewsTab.classList.add('active');
            mediaTab.classList.remove('active');
          } else {
            mediaTab.classList.add('active');
            reviewsTab.classList.remove('active');
          }
        });
      });
    });
