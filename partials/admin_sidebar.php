<?php // Usage: $active = 'dashboard'|'student'|'collection'|'payment'|'posts'|'enroll'|'trial'|'settings'|'profile'; ?>
<div class="sidebar offcanvas-md offcanvas-start" tabindex="-1" id="sidebar" role="navigation" aria-label="Main Sidebar" data-bs-backdrop="false">
  <div class="logo d-flex align-items-center gap-2">
    <img src="Picture/Logo2.png" alt="D'MARSIANS Logo" class="logo-img img-fluid" style="max-width:56px;height:auto;">
    <h2 class="m-0">D'MARSIANS<br>TAEKWONDO<br>SYSTEM</h2>
  </div>
  <nav>
    <a href="admin_dashboard.php" class="<?= isset($active) && $active==='dashboard'?'active':'' ?>"><i class="fas fa-th-large"></i><span>DASHBOARD</span></a>
    <a href="admin_student_management.php" class="<?= isset($active) && $active==='student'?'active':'' ?>"><i class="fas fa-user-graduate"></i><span>STUDENT MANAGEMENT</span></a>
    <a href="admin_collection.php" class="<?= isset($active) && $active==='collection'?'active':'' ?>"><i class="fas fa-money-bill-wave"></i><span>COLLECTION</span></a>
    <a href="admin_payment.php" class="<?= isset($active) && $active==='payment'?'active':'' ?>"><i class="fas fa-credit-card"></i><span>PAYMENT</span></a>
    <a href="admin_post_management.php" class="<?= isset($active) && $active==='posts'?'active':'' ?>"><i class="fas fa-bullhorn"></i><span>POST MANAGEMENT</span></a>
    <div class="dropdown">
      <a href="#" class="dropdown-toggle"><i class="fas fa-chart-bar"></i><span>ENROLLMENT REPORT</span></a>
      <div class="dropdown-content">
        <a href="admin_enrollment.php" class="<?= isset($active) && $active==='enroll'?'active':'' ?>"><i class="fas fa-user-plus"></i><span>ENROLLMENT</span></a>
        <a href="admin_trial_session.php" class="<?= isset($active) && $active==='trial'?'active':'' ?>"><i class="fas fa-users"></i><span>TRIAL SESSION</span></a>
      </div>
    </div>
    <a href="admin_settings.php" class="<?= isset($active) && $active==='settings'?'active':'' ?>"><i class="fas fa-cogs"></i><span>ADMIN SETTINGS</span></a>
    <a href="admin_profile.php" class="<?= isset($active) && $active==='profile'?'active':'' ?>"><i class="fas fa-user-circle"></i><span>PROFILE</span></a>
  </nav>
  <div class="logout-container">
    <a href="admin_logout.php" class="logout"><i class="fas fa-power-off"></i><span>Logout</span></a>
  </div>
</div>

<script>
// Aggressively remove offcanvas backdrop on desktop only (real-time removal)
(function() {
  function isDesktop() {
    return window.innerWidth >= 768;
  }
  
  function removeBackdrop() {
    if (!isDesktop()) return; // Only remove on desktop
    
    const backdrop = document.querySelector('.offcanvas-backdrop');
    if (backdrop) {
      backdrop.remove();
      return true;
    }
    return false;
  }
  
  // PREVENT BACKDROP CREATION AT SOURCE - Override Bootstrap's backdrop creation
  function setupBootstrapOverride() {
    if (typeof window.bootstrap === 'undefined' || !window.bootstrap.Offcanvas) {
      // Bootstrap not loaded yet, try again later
      setTimeout(setupBootstrapOverride, 50);
      return;
    }
    
    const Offcanvas = window.bootstrap.Offcanvas;
    const originalGetOrCreateInstance = Offcanvas.getOrCreateInstance;
    
    // Override getOrCreateInstance to prevent backdrop on desktop
    Offcanvas.getOrCreateInstance = function(element, config) {
      // Force backdrop to false on desktop for sidebar
      if (isDesktop() && element && element.id === 'sidebar') {
        config = config || {};
        config.backdrop = false;
      }
      
      const instance = originalGetOrCreateInstance.call(this, element, config);
      
      if (isDesktop() && instance && instance._element && instance._element.id === 'sidebar') {
        // Override _backdrop property BEFORE it's set
        let backdropValue = null;
        Object.defineProperty(instance, '_backdrop', {
          get: function() {
            return isDesktop() ? null : backdropValue;
          },
          set: function(value) {
            if (!isDesktop()) {
              backdropValue = value;
            } else {
              backdropValue = null;
              // Immediately remove if Bootstrap tries to set it
              if (value && value._element) {
                try {
                  value._element.remove();
                } catch(e) {}
              }
            }
          },
          configurable: true,
          enumerable: true
        });
        
        // Override the _getBackdrop method
        if (instance._getBackdrop) {
          const originalGetBackdrop = instance._getBackdrop.bind(instance);
          instance._getBackdrop = function() {
            if (isDesktop()) {
              return null;
            }
            return originalGetBackdrop();
          };
        }
        
        // Override show method to prevent backdrop
        if (instance.show) {
          const originalShow = instance.show.bind(instance);
          instance.show = function() {
            if (isDesktop()) {
              // Temporarily set backdrop config
              const originalBackdrop = this._config ? this._config.backdrop : undefined;
              if (this._config) {
                this._config.backdrop = false;
              }
              originalShow();
              if (this._config && originalBackdrop !== undefined) {
                this._config.backdrop = originalBackdrop;
              }
            } else {
              originalShow();
            }
          };
        }
      }
      
      return instance;
    };
    
    // Intercept existing instances immediately
    const sidebarEl = document.getElementById('sidebar');
    if (sidebarEl && isDesktop()) {
      try {
        const instance = Offcanvas.getInstance(sidebarEl);
        if (instance) {
          instance._backdrop = null;
          if (instance._backdrop && instance._backdrop._element) {
            instance._backdrop._element.remove();
          }
        }
      } catch(e) {
        // Ignore errors
      }
    }
  }
  
  // Try to setup immediately and also on DOMContentLoaded
  setupBootstrapOverride();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupBootstrapOverride);
  }
  
  // Remove immediately if it exists
  removeBackdrop();
  
  // Real-time removal using requestAnimationFrame for instant detection
  function checkAndRemove() {
    if (isDesktop()) {
      removeBackdrop();
      requestAnimationFrame(checkAndRemove);
    }
  }
  requestAnimationFrame(checkAndRemove);
  
  // ENSURE data-bs-backdrop="false" is always set on desktop
  function enforceBackdropAttribute() {
    if (isDesktop()) {
      const sidebarEl = document.getElementById('sidebar');
      if (sidebarEl) {
        sidebarEl.setAttribute('data-bs-backdrop', 'false');
        sidebarEl.dataset.bsBackdrop = 'false';
      }
    }
  }
  enforceBackdropAttribute();
  setInterval(enforceBackdropAttribute, 100);
  
  // INTERCEPT DOM APPEND OPERATIONS - Prevent backdrop from being added to document.body
  if (isDesktop() && document.body) {
    const bodyAppendChild = document.body.appendChild.bind(document.body);
    const bodyInsertBefore = document.body.insertBefore.bind(document.body);
    
    document.body.appendChild = function(child) {
      if (isDesktop() && child && child.classList && child.classList.contains('offcanvas-backdrop')) {
        child.remove(); // Remove immediately
        return child; // Don't append backdrop to body on desktop
      }
      return bodyAppendChild(child);
    };
    
    document.body.insertBefore = function(newNode, referenceNode) {
      if (isDesktop() && newNode && newNode.classList && newNode.classList.contains('offcanvas-backdrop')) {
        newNode.remove(); // Remove immediately
        return newNode; // Don't insert backdrop to body on desktop
      }
      return bodyInsertBefore(newNode, referenceNode);
    };
  }
  
  // Watch for backdrop creation using MutationObserver (most efficient)
  const observer = new MutationObserver(function(mutations) {
    if (!isDesktop()) return;
    
    mutations.forEach(function(mutation) {
      mutation.addedNodes.forEach(function(node) {
        if (node.nodeType === 1) { // Element node
          if (node.classList && node.classList.contains('offcanvas-backdrop')) {
            node.remove();
          }
          // Also check children
          const backdrop = node.querySelector && node.querySelector('.offcanvas-backdrop');
          if (backdrop) {
            backdrop.remove();
          }
        }
      });
    });
  });
  
  observer.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: false,
    characterData: false
  });
  
  // Intercept Bootstrap events for immediate removal
  document.addEventListener('show.bs.offcanvas', function(e) {
    if (e && e.target && e.target.id === 'sidebar' && isDesktop()) {
      // Remove immediately and repeatedly
      removeBackdrop();
      requestAnimationFrame(() => removeBackdrop());
      setTimeout(removeBackdrop, 0);
      setTimeout(removeBackdrop, 1);
      setTimeout(removeBackdrop, 5);
      setTimeout(removeBackdrop, 10);
      setTimeout(removeBackdrop, 20);
      setTimeout(removeBackdrop, 50);
    }
  });
  
  document.addEventListener('shown.bs.offcanvas', function(e) {
    if (e && e.target && e.target.id === 'sidebar' && isDesktop()) {
      removeBackdrop();
      requestAnimationFrame(() => removeBackdrop());
    }
  });
  
  document.addEventListener('hide.bs.offcanvas', function(e) {
    if (e && e.target && e.target.id === 'sidebar' && isDesktop()) {
      removeBackdrop();
    }
  });
  
  // Handle window resize
  let resizeTimeout;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
      if (isDesktop()) {
        removeBackdrop();
      }
    }, 100);
  });
  
  // Periodic check as fallback (faster interval for desktop)
  setInterval(function() {
    if (isDesktop()) {
      removeBackdrop();
      // Also check all offcanvas-backdrop elements and remove them
      document.querySelectorAll('.offcanvas-backdrop').forEach(function(backdrop) {
        backdrop.remove();
      });
    }
  }, 10); // Check every 10ms on desktop for maximum responsiveness
  
  // Additional aggressive removal - check immediately on any DOM change
  const aggressiveObserver = new MutationObserver(function() {
    if (isDesktop()) {
      const backdrops = document.querySelectorAll('.offcanvas-backdrop');
      backdrops.forEach(function(backdrop) {
        backdrop.remove();
      });
    }
  });
  
  if (document.body) {
    aggressiveObserver.observe(document.body, {
      childList: true,
      subtree: true,
      attributes: false
    });
  }
})();
</script>
