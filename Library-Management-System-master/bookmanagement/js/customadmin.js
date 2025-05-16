


document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const formModal = document.getElementById('formModal');
    const modalForm = document.getElementById('modalForm');
    const addBookBtn = document.getElementById('addBookBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const closeBtn = document.querySelector('.close-btn');
    const searchInput = document.getElementById('searchInput');
    const booksTableBody = document.getElementById('booksTableBody');
    const logoutBtn = document.getElementById('logoutBtn');
    const navItems = document.querySelectorAll('.sidebar-nav li');
    const topBarTitle = document.querySelector('.top-bar h1');

     // Open modal to add a new book
     addBookBtn.addEventListener('click', () => openModalForNewBook());
        
     // Close modal buttons
     closeBtn.addEventListener('click', closeModal);
     cancelBtn.addEventListener('click', closeModal);
     
     // Click outside modal to close
     window.addEventListener('click', (e) => {
         if (e.target === formModal) {
             closeModal();
         }
     });
     
     // Open modal for form
     function openModalForNewBook() {
        modalForm.reset();
        formModal.style.display = 'block';
    }

     // Close the modal
     function closeModal() {
        formModal.style.display = 'none';
        modalForm.reset();
    }

    function setupEventListeners() {
        // Open modal to add a new book
        addBookBtn.addEventListener('click', () => openModalForNewBook());
        
        // Close modal buttons
        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        
        // Click outside modal to close
        window.addEventListener('click', (e) => {
            if (e.target === formModal) {
                closeModal();
            }
        });
        
        
      
    }

     
})



