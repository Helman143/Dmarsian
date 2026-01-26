<?php // Usage: $active = 'dashboard'|'student'|'collection'|'payment'|'posts'|'enroll'|'trial'; ?>
<div class="sidebar offcanvas-md offcanvas-start" tabindex="-1" id="sidebar" role="navigation" aria-label="Main Sidebar" data-bs-backdrop="false">
  <div class="logo d-flex align-items-center gap-2">
    <img src="Picture/Logo2.png" alt="D'MARSIANS Logo" class="logo-img img-fluid" style="max-width:56px;height:auto;">
    <h2 class="m-0">D'MARSIANS<br>TAEKWONDO<br>SYSTEM</h2>
  </div>
  <nav>
    <a href="dashboard.php" class="<?= isset($active) && $active==='dashboard'?'active':'' ?>"><i class="fas fa-th-large"></i><span>DASHBOARD</span></a>
    <a href="student_management.php" class="<?= isset($active) && $active==='student'?'active':'' ?>"><i class="fas fa-user-graduate"></i><span>STUDENT MANAGEMENT</span></a>
    <a href="collection.php" class="<?= isset($active) && $active==='collection'?'active':'' ?>"><i class="fas fa-money-bill-wave"></i><span>COLLECTION</span></a>
    <a href="payment.php" class="<?= isset($active) && $active==='payment'?'active':'' ?>"><i class="fas fa-credit-card"></i><span>PAYMENT</span></a>
    <a href="post_management.php" class="<?= isset($active) && $active==='posts'?'active':'' ?>"><i class="fas fa-bullhorn"></i><span>POST MANAGEMENT</span></a>
    <div class="dropdown">
      <a href="#" class="dropdown-toggle"><i class="fas fa-chart-bar"></i><span>ENROLLMENT REPORT</span></a>
      <div class="dropdown-content">
        <a href="enrollment.php" class="<?= isset($active) && $active==='enroll'?'active':'' ?>"><i class="fas fa-user-plus"></i><span>ENROLLMENT</span></a>
        <a href="trial_session.php" class="<?= isset($active) && $active==='trial'?'active':'' ?>"><i class="fas fa-users"></i><span>TRIAL SESSION</span></a>
      </div>
    </div>
  </nav>
  <div class="logout-container">
    <a href="logout.php" class="logout"><i class="fas fa-power-off"></i><span>Logout</span></a>
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
  if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Offcanvas) {
    const Offcanvas = window.bootstrap.Offcanvas;
    const originalGetOrCreateInstance = Offcanvas.getOrCreateInstance;
    
    // Override getOrCreateInstance to prevent backdrop on desktop
    Offcanvas.getOrCreateInstance = function(element, config) {
      const instance = originalGetOrCreateInstance.call(this, element, config);
      
      if (isDesktop() && instance && instance._element && instance._element.id === 'sidebar') {
        // Override the _getBackdrop method to return null on desktop
        if (instance._getBackdrop) {
          const originalGetBackdrop = instance._getBackdrop.bind(instance);
          instance._getBackdrop = function() {
            if (isDesktop()) {
              return null; // Prevent backdrop creation
            }
            return originalGetBackdrop();
          };
        }
        
        // Override backdrop property
        if (instance._backdrop) {
          Object.defineProperty(instance, '_backdrop', {
            get: function() {
              return isDesktop() ? null : this._backdrop;
            },
            set: function(value) {
              if (!isDesktop()) {
                this._backdrop = value;
              }
            },
            configurable: true
          });
        }
      }
      
      return instance;
    };
    
    // Intercept existing instances
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarEl = document.getElementById('sidebar');
      if (sidebarEl && isDesktop()) {
        try {
          const instance = Offcanvas.getInstance(sidebarEl) || Offcanvas.getOrCreateInstance(sidebarEl);
          if (instance && instance._backdrop) {
            instance._backdrop = null;
          }
        } catch(e) {
          // Ignore errors
        }
      }
    });
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
  
  // INTERCEPT DOM APPEND OPERATIONS - Prevent backdrop from being added to document.body
  if (isDesktop() && document.body) {
    const bodyAppendChild = document.body.appendChild.bind(document.body);
    const bodyInsertBefore = document.body.insertBefore.bind(document.body);
    
    document.body.appendChild = function(child) {
      if (isDesktop() && child && child.classList && child.classList.contains('offcanvas-backdrop')) {
        return child; // Don't append backdrop to body on desktop
      }
      return bodyAppendChild(child);
    };
    
    document.body.insertBefore = function(newNode, referenceNode) {
      if (isDesktop() && newNode && newNode.classList && newNode.classList.contains('offcanvas-backdrop')) {
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
    }
  }, 50); // Check every 50ms on desktop
})();
</script>
