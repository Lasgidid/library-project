document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const bookModal = document.getElementById('bookModal');
    const modalTitle = document.getElementById('modalTitle');
    const bookForm = document.getElementById('bookForm');
    const addBookBtn = document.getElementById('addBookBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const closeBtn = document.querySelector('.close-btn');
    const searchInput = document.getElementById('searchInput');
    const booksTableBody = document.getElementById('booksTableBody');
    const logoutBtn = document.getElementById('logoutBtn');
    const navItems = document.querySelectorAll('.sidebar-nav li');
    const topBarTitle = document.querySelector('.top-bar h1');
    
    // Sample book data (would normally come from a database)
    let books = [
        {
            id: 1,
            title: 'To Kill a Mockingbird',
            author: 'Harper Lee',
            genre: 'Fiction',
            status: 'Available',
            cover: 'https://m.media-amazon.com/images/I/41j-s9fHJcL.jpg'
        },
        {
            id: 2,
            title: 'A Brief History of Time',
            author: 'Stephen Hawking',
            genre: 'Science',
            status: 'Borrowed',
            cover: 'https://m.media-amazon.com/images/I/51+GySc8ExL.jpg'
        },
        {
            id: 3,
            title: '1984',
            author: 'George Orwell',
            genre: 'Fiction',
            status: 'Available',
            cover: 'https://m.media-amazon.com/images/I/41DoIQLHzlL.jpg'
        }
    ];
    
    // Sample users data
    let users = [
        { id: 1, name: 'John Doe', email: 'john.doe@example.com', role: 'Student', status: 'Active' },
        { id: 2, name: 'Jane Smith', email: 'jane.smith@example.com', role: 'Teacher', status: 'Active' },
        { id: 3, name: 'Alice Johnson', email: 'alice.j@example.com', role: 'Student', status: 'Inactive' }
    ];
    
    // Sample borrowed books data
    let borrowedBooks = [
        { id: 1, bookTitle: 'A Brief History of Time', borrower: 'Jane Smith', borrowDate: '2025-02-01', dueDate: '2025-03-01', status: 'Overdue' },
        { id: 2, bookTitle: 'The Great Gatsby', borrower: 'John Doe', borrowDate: '2025-02-15', dueDate: '2025-03-15', status: 'On Time' }
    ];
    
    // Current book being edited (null for new books)
    let currentBookId = null;
    
    // Current active section
    let currentSection = 'books';
    
    // Initialize the dashboard
    function init() {
        createDashboardSections();
        renderBooks();
        setupEventListeners();
        // Set Books as the default active section
        showSection('books');
    }
    
    // Create dashboard sections
    function createDashboardSections() {
        const mainContent = document.querySelector('.main-content');
        
        // Get existing elements
        const topBar = document.querySelector('.top-bar');
        const actionBar = document.querySelector('.action-bar');
        const tableContainer = document.querySelector('.table-container');
        
        // Create container for all content sections
        const sectionsContainer = document.createElement('div');
        sectionsContainer.className = 'sections-container';
        
        // Move existing books content to the books section
        const booksSection = document.createElement('div');
        booksSection.id = 'books-section';
        booksSection.className = 'dashboard-section';
        // Keep existing elements in the DOM, but move them to our section
        booksSection.appendChild(actionBar);
        booksSection.appendChild(tableContainer);
        
        // Create users section
        const usersSection = document.createElement('div');
        usersSection.id = 'users-section';
        usersSection.className = 'dashboard-section';
        usersSection.innerHTML = `
            <div class="action-bar">
                <button id="addUserBtn" class="primary-btn">
                    <i class="fas fa-plus"></i> Add New User
                </button>
                <div class="search-bar">
                    <input type="text" id="searchUserInput" placeholder="Search users...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Users will be dynamically added here -->
                    </tbody>
                </table>
            </div>
        `;
        
        // Create borrowed books section
        const borrowedSection = document.createElement('div');
        borrowedSection.id = 'borrowed-section';
        borrowedSection.className = 'dashboard-section';
        borrowedSection.innerHTML = `
            <div class="action-bar">
                <button id="addBorrowingBtn" class="primary-btn">
                    <i class="fas fa-plus"></i> Record New Borrowing
                </button>
                <div class="search-bar">
                    <input type="text" id="searchBorrowedInput" placeholder="Search borrowed books...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="table-container">
                <table class="borrowed-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Borrower</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="borrowedTableBody">
                        <!-- Borrowed books will be dynamically added here -->
                    </tbody>
                </table>
            </div>
        `;
        
        // Create settings section
        const settingsSection = document.createElement('div');
        settingsSection.id = 'settings-section';
        settingsSection.className = 'dashboard-section';
        settingsSection.innerHTML = `
            <div class="settings-container">
                <h2>System Settings</h2>
                <form id="settingsForm">
                    <div class="form-group">
                        <label for="libraryName">Library Name</label>
                        <input type="text" id="libraryName" value="Central Library" class="settings-input">
                    </div>
                    <div class="form-group">
                        <label for="maxBorrowDays">Maximum Borrow Days</label>
                        <input type="number" id="maxBorrowDays" value="30" class="settings-input">
                    </div>
                    <div class="form-group">
                        <label for="maxBooksPerUser">Maximum Books Per User</label>
                        <input type="number" id="maxBooksPerUser" value="5" class="settings-input">
                    </div>
                    <div class="form-group">
                        <label for="finePerDay">Fine Per Day (Late Returns)</label>
                        <input type="number" id="finePerDay" value="1" step="0.5" class="settings-input">
                    </div>
                    <div class="form-group">
                        <label for="emailNotifications">Email Notifications</label>
                        <select id="emailNotifications" class="settings-input">
                            <option value="enabled">Enabled</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                    <button type="submit" class="primary-btn">Save Settings</button>
                </form>
            </div>
        `;
        
        // Add all sections to the container
        sectionsContainer.appendChild(booksSection);
        sectionsContainer.appendChild(usersSection);
        sectionsContainer.appendChild(borrowedSection);
        sectionsContainer.appendChild(settingsSection);
        
        // Insert the sections container after the top bar
        mainContent.insertBefore(sectionsContainer, topBar.nextSibling);
        
        // Add styles for sections
        const style = document.createElement('style');
        style.textContent = `
            .dashboard-section {
                display: none;
            }
            
            .dashboard-section.active {
                display: block;
            }
            
            .settings-container {
                background: white;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                padding: 2rem;
            }
            
            .settings-container h2 {
                margin-bottom: 1.5rem;
                color: #333;
                font-family: 'Poppins', sans-serif;
            }
            
            .settings-input {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-family: 'Inter', sans-serif;
                margin-bottom: 1.5rem;
            }
            
            #settingsForm .primary-btn {
                margin-top: 1rem;
            }
            
            .text-success {
                color: #4CAF50;
                font-weight: 500;
            }
            
            .text-warning {
                color: #FFC107;
                font-weight: 500;
            }
            
            .text-danger {
                color: #FF5252;
                font-weight: 500;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Event Listeners
    function setupEventListeners() {
        // Open modal to add a new book
        addBookBtn.addEventListener('click', () => openModalForNewBook());
        
        // Close modal buttons
        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        
        // Click outside modal to close
        window.addEventListener('click', (e) => {
            if (e.target === bookModal) {
                closeModal();
            }
        });
        
        // Form submission
        bookForm.addEventListener('submit', handleFormSubmit);
        
        // Search functionality
        searchInput.addEventListener('input', handleSearch);
        
        // Logout button
        logoutBtn.addEventListener('click', handleLogout);
        
        // Settings form submission
        const settingsForm = document.getElementById('settingsForm');
        if (settingsForm) {
            settingsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Settings saved successfully!');
            });
        }
        
        // Sidebar navigation
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                const sectionId = this.querySelector('a').textContent.trim().toLowerCase();
                
                // Update active class on nav items
                navItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding section
                showSection(sectionId);
            });
        });
        
        // Setup additional search inputs for other sections
        const searchUserInput = document.getElementById('searchUserInput');
        if (searchUserInput) {
            searchUserInput.addEventListener('input', function() {
                renderUsers(filterUsers(this.value));
            });
        }
        
        const searchBorrowedInput = document.getElementById('searchBorrowedInput');
        if (searchBorrowedInput) {
            searchBorrowedInput.addEventListener('input', function() {
                renderBorrowedBooks(filterBorrowedBooks(this.value));
            });
        }
        
        // Add User button
        const addUserBtn = document.getElementById('addUserBtn');
        if (addUserBtn) {
            addUserBtn.addEventListener('click', function() {
                alert('Add User functionality will be implemented here');
            });
        }
        
        // Add Borrowing button
        const addBorrowingBtn = document.getElementById('addBorrowingBtn');
        if (addBorrowingBtn) {
            addBorrowingBtn.addEventListener('click', function() {
                alert('Record New Borrowing functionality will be implemented here');
            });
        }
    }
    
    // Show the selected section and update page title
    function showSection(sectionId) {
        const sections = document.querySelectorAll('.dashboard-section');
        sections.forEach(section => section.classList.remove('active'));
        
        let title = '';
        
        switch(sectionId) {
            case 'books':
                document.getElementById('books-section').classList.add('active');
                title = 'Books Management';
                if (currentSection !== 'books') {
                    renderBooks();
                }
                break;
            case 'users':
                document.getElementById('users-section').classList.add('active');
                title = 'Users Management';
                if (currentSection !== 'users') {
                    renderUsers();
                }
                break;
            case 'borrowed':
                document.getElementById('borrowed-section').classList.add('active');
                title = 'Borrowed Books';
                if (currentSection !== 'borrowed') {
                    renderBorrowedBooks();
                }
                break;
            case 'settings':
                document.getElementById('settings-section').classList.add('active');
                title = 'System Settings';
                break;
            default:
                document.getElementById('books-section').classList.add('active');
                title = 'Books Management';
                if (currentSection !== 'books') {
                    renderBooks();
                }
        }
        
        topBarTitle.textContent = title;
        currentSection = sectionId;
    }
    
    // Render books table
    function renderBooks(booksToRender = books) {
        booksTableBody.innerHTML = '';
        
        if (booksToRender.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `
                <td colspan="6" style="text-align: center; padding: 2rem;">
                    No books found. Add a new book or adjust your search.
                </td>
            `;
            booksTableBody.appendChild(emptyRow);
            return;
        }
        
        booksToRender.forEach(book => {
            const row = document.createElement('tr');
            
            // Default cover image if none provided
            const coverUrl = book.cover || 'https://via.placeholder.com/50x70?text=No+Cover';
            
            // Set status class for styling
            const statusClass = book.status === 'Available' ? 'text-success' : 'text-warning';
            
            row.innerHTML = `
                <td><img src="${coverUrl}" alt="${book.title} cover"></td>
                <td>${book.title}</td>
                <td>${book.author}</td>
                <td>${book.genre}</td>
                <td><span class="${statusClass}">${book.status}</span></td>
                <td class="action-buttons">
                    <button class="edit-btn" data-id="${book.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="delete-btn" data-id="${book.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            booksTableBody.appendChild(row);
        });
        
        // Add event listeners to edit and delete buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const bookId = parseInt(btn.getAttribute('data-id'));
                openModalForEdit(bookId);
            });
        });
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const bookId = parseInt(btn.getAttribute('data-id'));
                confirmDeleteBook(bookId);
            });
        });
    }
    
    // Render users table
    function renderUsers(usersToRender = users) {
        const usersTableBody = document.getElementById('usersTableBody');
        if (!usersTableBody) return;
        
        usersTableBody.innerHTML = '';
        
        if (usersToRender.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `
                <td colspan="5" style="text-align: center; padding: 2rem;">
                    No users found. Add a new user or adjust your search.
                </td>
            `;
            usersTableBody.appendChild(emptyRow);
            return;
        }
        
        usersToRender.forEach(user => {
            const row = document.createElement('tr');
            
            // Set status class for styling
            const statusClass = user.status === 'Active' ? 'text-success' : 'text-danger';
            
            row.innerHTML = `
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.role}</td>
                <td><span class="${statusClass}">${user.status}</span></td>
                <td class="action-buttons">
                    <button class="edit-btn" data-id="${user.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="delete-btn" data-id="${user.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            usersTableBody.appendChild(row);
        });
        
        // Add event listeners
        document.querySelectorAll('#usersTableBody .edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                alert('Edit user functionality will be implemented here');
            });
        });
        
        document.querySelectorAll('#usersTableBody .delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                alert('Delete user functionality will be implemented here');
            });
        });
    }
    
    // Render borrowed books table
    function renderBorrowedBooks(borrowedToRender = borrowedBooks) {
        const borrowedTableBody = document.getElementById('borrowedTableBody');
        if (!borrowedTableBody) return;
        
        borrowedTableBody.innerHTML = '';
        
        if (borrowedToRender.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `
                <td colspan="6" style="text-align: center; padding: 2rem;">
                    No borrowed books found. Record a new borrowing or adjust your search.
                </td>
            `;
            borrowedTableBody.appendChild(emptyRow);
            return;
        }
        
        borrowedToRender.forEach(item => {
            const row = document.createElement('tr');
            
            // Set status class for styling
            const statusClass = item.status === 'On Time' ? 'text-success' : 'text-danger';
            
            row.innerHTML = `
                <td>${item.bookTitle}</td>
                <td>${item.borrower}</td>
                <td>${item.borrowDate}</td>
                <td>${item.dueDate}</td>
                <td><span class="${statusClass}">${item.status}</span></td>
                <td class="action-buttons">
                    <button class="edit-btn" data-id="${item.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="return-btn" data-id="${item.id}" title="Mark as Returned">
                        <i class="fas fa-undo"></i>
                    </button>
                </td>
            `;
            
            borrowedTableBody.appendChild(row);
        });
        
        // Add event listeners
        document.querySelectorAll('#borrowedTableBody .edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                alert('Edit borrowing details functionality will be implemented here');
            });
        });
        
        document.querySelectorAll('#borrowedTableBody .return-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                alert('Mark as returned functionality will be implemented here');
            });
        });
    }
    
    // Filter users based on search term
    function filterUsers(searchTerm) {
        if (!searchTerm.trim()) return users;
        
        searchTerm = searchTerm.toLowerCase().trim();
        return users.filter(user => 
            user.name.toLowerCase().includes(searchTerm) || 
            user.email.toLowerCase().includes(searchTerm) ||
            user.role.toLowerCase().includes(searchTerm)
        );
    }
    
    // Filter borrowed books based on search term
    function filterBorrowedBooks(searchTerm) {
        if (!searchTerm.trim()) return borrowedBooks;
        
        searchTerm = searchTerm.toLowerCase().trim();
        return borrowedBooks.filter(item => 
            item.bookTitle.toLowerCase().includes(searchTerm) || 
            item.borrower.toLowerCase().includes(searchTerm) ||
            item.status.toLowerCase().includes(searchTerm)
        );
    }
    
    // Search functionality for books
    function handleSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        if (searchTerm === '') {
            renderBooks(); // Show all books if search is empty
            return;
        }
        
        const filteredBooks = books.filter(book => 
            book.title.toLowerCase().includes(searchTerm) || 
            book.author.toLowerCase().includes(searchTerm) ||
            book.genre.toLowerCase().includes(searchTerm)
        );
        
        renderBooks(filteredBooks);
    }
    
    // Open modal for adding a new book
    function openModalForNewBook() {
        modalTitle.textContent = 'Add New Book';
        currentBookId = null;
        bookForm.reset();
        bookModal.style.display = 'block';
    }
    
    // Open modal for editing an existing book
    function openModalForEdit(bookId) {
        const book = books.find(b => b.id === bookId);
        if (!book) return;
        
        modalTitle.textContent = 'Edit Book';
        currentBookId = bookId;
        
        // Fill form with book data
        document.getElementById('bookTitle').value = book.title;
        document.getElementById('bookAuthor').value = book.author;
        document.getElementById('bookGenre').value = book.genre;
        document.getElementById('bookCover').value = book.cover || '';
        
        bookModal.style.display = 'block';
    }
    
    // Close the modal
    function closeModal() {
        bookModal.style.display = 'none';
        bookForm.reset();
    }
    
    // Handle form submission (add or edit book)
    function handleFormSubmit(e) {
        e.preventDefault();
        
        const bookData = {
            title: document.getElementById('bookTitle').value,
            author: document.getElementById('bookAuthor').value,
            genre: document.getElementById('bookGenre').value,
            cover: document.getElementById('bookCover').value,
            status: 'Available' // Default status for new books
        };
        
        if (currentBookId === null) {
            // Add new book
            addBook(bookData);
        } else {
            // Update existing book
            updateBook(currentBookId, bookData);
        }
        
        closeModal();
    }
    
    // Add a new book
    function addBook(bookData) {
        // Generate a new ID (would be handled by the backend in a real app)
        const newId = books.length > 0 ? Math.max(...books.map(b => b.id)) + 1 : 1;
        
        const newBook = {
            id: newId,
            ...bookData
        };
        
        books.push(newBook);
        renderBooks();
        alert('Book added successfully!');
    }
    
    // Update an existing book
    function updateBook(bookId, bookData) {
        const index = books.findIndex(b => b.id === bookId);
        if (index === -1) return;
        
        // Preserve the status of existing book
        bookData.status = books[index].status;
        
        books[index] = {
            ...books[index],
            ...bookData
        };
        
        renderBooks();
        alert('Book updated successfully!');
    }
    
    // Confirm before deleting a book
    function confirmDeleteBook(bookId) {
        const book = books.find(b => b.id === bookId);
        if (!book) return;
        
        const isConfirmed = confirm(`Are you sure you want to delete "${book.title}"?`);
        if (isConfirmed) {
            deleteBook(bookId);
        } 
    }
    
    // Delete a book
    function deleteBook(bookId) {
        books = books.filter(b => b.id !== bookId);
        renderBooks();
        alert('Book deleted successfully!');
    }
    
    // Handle logout
    function handleLogout() {
        const isConfirmed = confirm('Are you sure you want to logout?');
        if (isConfirmed) {
            // In a real application, you would clear session/localStorage and redirect
            alert('Logging out...');
            window.location.href = 'index.html'; // Redirect to login page
        }
    }
    
    // Initialize the dashboard
    init();
});