/**
 * Client Details Page - JavaScript
 * Handles tab navigation, lazy loading, keyboard shortcuts, and interactivity
 *
 * @package    Tija CRM
 * @subpackage Client Management
 * @version    2.0.0
 */

(function() {
   'use strict';

   // =========================================================================
   // TAB NAVIGATION & LAZY LOADING
   // =========================================================================

   const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
   const loadedTabs = new Set(['overview']); // Overview loaded by default

   // Tab content loading
   tabButtons.forEach(button => {
      button.addEventListener('shown.bs.tab', function(e) {
         const targetId = e.target.getAttribute('data-bs-target').replace('#', '');
         const tabPane = document.getElementById(targetId);

         if(tabPane && tabPane.dataset.lazy === 'true' && !loadedTabs.has(targetId)) {
            loadTabContent(targetId, tabPane);
            loadedTabs.add(targetId);
         }

         // Save active tab to session storage
         sessionStorage.setItem('clientDetailsActiveTab', targetId);
      });
   });

   // Restore previously active tab
   function restoreActiveTab() {
      const savedTab = sessionStorage.getItem('clientDetailsActiveTab');
      if(savedTab && savedTab !== 'overview') {
         const tabButton = document.querySelector(`[data-bs-target="#${savedTab}"]`);
         if(tabButton) {
            const tab = new bootstrap.Tab(tabButton);
            tab.show();
         }
      }
   }

   // Load tab content via AJAX
   function loadTabContent(tabId, tabPane) {
      const loadingDiv = tabPane.querySelector('.tab-loading');
      if(loadingDiv) {
         loadingDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
      }

      // Content would be loaded here if using AJAX
      // For now, content is directly included in PHP
   }

   // =========================================================================
   // KEYBOARD SHORTCUTS
   // =========================================================================

   document.addEventListener('keydown', function(e) {
      // Don't trigger if user is typing in an input
      if(['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
         return;
      }

      // Alt + key combinations
      if(e.altKey) {
         switch(e.key.toLowerCase()) {
            case 'o': // Alt + O: Overview
               e.preventDefault();
               document.querySelector('#overview-tab')?.click();
               break;
            case 'c': // Alt + C: Contacts
               e.preventDefault();
               document.querySelector('#contacts-tab')?.click();
               break;
            case 's': // Alt + S: Sales & Projects
               e.preventDefault();
               document.querySelector('#sales-projects-tab')?.click();
               break;
            case 'd': // Alt + D: Documents
               e.preventDefault();
               document.querySelector('#documents-tab')?.click();
               break;
            case 'a': // Alt + A: Activities
               e.preventDefault();
               document.querySelector('#activities-tab')?.click();
               break;
            case 'r': // Alt + R: Relationships
               e.preventDefault();
               document.querySelector('#relationships-tab')?.click();
               break;
            case 'f': // Alt + F: Financials
               e.preventDefault();
               document.querySelector('#financials-tab')?.click();
               break;
            case 'e': // Alt + E: Edit client
               e.preventDefault();
               const editCollapse = document.querySelector('#editClientDetails');
               if(editCollapse) {
                  const bsCollapse = new bootstrap.Collapse(editCollapse, { toggle: true });
               }
               break;
            case 'n': // Alt + N: New contact
               e.preventDefault();
               const contactModal = document.querySelector('#manageClientContactModal');
               if(contactModal) {
                  const bsModal = new bootstrap.Modal(contactModal);
                  bsModal.show();
               }
               break;
         }
      }
   });

   // =========================================================================
   // QUICK SEARCH FUNCTIONALITY
   // =========================================================================

   window.quickSearch = function(searchTerm) {
      searchTerm = searchTerm.toLowerCase();

      // Search contacts
      document.querySelectorAll('.contact-card').forEach(card => {
         const text = card.textContent.toLowerCase();
         card.closest('.col-md-6').style.display = text.includes(searchTerm) ? '' : 'none';
      });

      // Search documents
      document.querySelectorAll('.document-card').forEach(card => {
         const text = card.textContent.toLowerCase();
         card.closest('.col-lg-4').style.display = text.includes(searchTerm) ? '' : 'none';
      });
   };

   // =========================================================================
   // CARD HOVER EFFECTS & ANIMATIONS
   // =========================================================================

   function initializeCardEffects() {
      const cards = document.querySelectorAll('.contact-card, .address-card, .activity-card, .document-card');
      cards.forEach(card => {
         card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
         });
         card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
         });
      });
   }

   // =========================================================================
   // TOOLTIP INITIALIZATION
   // =========================================================================

   function initializeTooltips() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
         return new bootstrap.Tooltip(tooltipTriggerEl);
      });
   }

   // =========================================================================
   // INITIALIZE ON PAGE LOAD
   // =========================================================================

   // Wait for DOM to be fully loaded
   if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initialize);
   } else {
      initialize();
   }

   function initialize() {
      restoreActiveTab();
      initializeCardEffects();
      initializeTooltips();

      console.log('Client Details v2.0 initialized');
      console.log('Keyboard shortcuts: Alt+O (Overview), Alt+C (Contacts), Alt+S (Sales), Alt+D (Documents), Alt+A (Activities), Alt+R (Relationships), Alt+F (Financials), Alt+E (Edit), Alt+N (New Contact)');
   }

})();

