document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const bookModal = document.getElementById('bookModal');
    const closeBtn = document.querySelector('.close-btn');
    const searchInput = document.getElementById('searchInput');
    const genreFilter = document.getElementById('genreFilter');
    const availabilityFilter = document.getElementById('availabilityFilter');
    const booksGrid = document.getElementById('booksGrid');
    const logoutBtn = document.getElementById('logoutBtn');
    const navItems = document.querySelectorAll('.sidebar-nav li');
    const borrowBtn = document.getElementById('borrowBtn');
    const sections = document.querySelectorAll('.dashboard-section');

    // Sample books data (would normally come from an API)
    let books = [
        {
            id: 1,
            title: 'To Kill a Mockingbird',
            author: 'Harper Lee',
            genre: 'Fiction',
            status: 'Available',
            cover: 'https://m.media-amazon.com/images/I/41j-s9fHJcL.jpg',
            description: 'A gripping, heart-wrenching, and wholly remarkable tale of coming-of-age in a South poisoned by virulent prejudice.',
            isbn: '978-0446310789',
            published: '1960',
            publisher: 'Grand Central Publishing'
        },
        {
            id: 2,
            title: 'A Brief History of Time',
            author: 'Stephen Hawking',
            genre: 'Science',
            status: 'Borrowed',
            cover: 'https://m.media-amazon.com/images/I/51+GySc8ExL.jpg',
            description: 'A landmark volume in science writing by one of the great minds of our time.',
            isbn: '978-0553380163',
            published: '1988',
            publisher: 'Bantam'
        },
        {
            id: 3,
            title: '1984',
            author: 'George Orwell',
            genre: 'Fiction',
            status: 'Available',
            cover: 'https://m.media-amazon.com/images/I/41DoIQLHzlL.jpg',
            description: 'A dystopian social science fiction novel and cautionary tale.',
            isbn: '978-0451524935',
            published: '1949',
            publisher: 'Signet Classic'
        }
    ];

    // Sample borrowed books data
    let borrowedBooks = [
        {
            id: 1,
            title: 'A Brief History of Time',
            author: 'Stephen Hawking',
            cover: 'https://m.media-amazon.com/images/I/51+GySc8ExL.jpg',
            borrowDate: '2025-02-01',
            dueDate: '2025-03-01',
            status: 'Overdue'
        }
    ];

    // Sample history data
    let borrowHistory = [
        {
            id: 1,
            title: 'The Great Gatsby',
            author: 'F. Scott Fitzgerald',
            cover: 'https://m.media-amazon.com/images/I/41iers+HLSL.jpg',
            borrowDate: '2025-01-01',
            returnDate: '2025-01-15',
            status: 'Returned'
        }
    ];

    // Initialize the dashboard
    function init() {
        renderBooks();
        setupEventListeners();
        showSection('browse');
    }

    // Event Listeners
    function setupEventListeners() {
        // Search and filter functionality
        searchInput.addEventListener('input', handleSearch);
        genreFilter.addEventListener('change', handleFilters);
        availabilityFilter.addEventListener('change', handleFilters);

        // Modal close buttons
        closeBtn.addEventListener('click', closeModal);
        document.getElementById('closeBtn').addEventListener('click', closeModal);

        // Click outside modal to close
        window.addEventListener('click', (e) => {
            if (e.target === bookModal) {
                closeModal();
            }
        });

        // Logout button
        logoutBtn.addEventListener('click', handleLogout);

        // Navigation
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                // Get the section name from the text content
                const text = this.querySelector('a').textContent.trim().toLowerCase();
                let sectionId;
                
                // Map the nav text to section IDs
                switch(text) {
                    case 'browse books':
                        sectionId = 'browse';
                        break;
                    case 'my borrowed':
                        sectionId = 'borrowed';
                        break;
                    case 'history':
                        sectionId = 'history';
                        break;
                    case 'profile':
                        sectionId = 'profile';
                        break;
                    default:
                        sectionId = 'browse';
                }
                
                // Update active class
                navItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');

                // Show corresponding section
                showSection(sectionId);
            });
        });

        // Borrow button in modal
        borrowBtn.addEventListener('click', handleBorrow);
    }

    // Show the selected section
    function showSection(sectionId) {
        sections.forEach(section => section.classList.remove('active'));
        
        let title = '';
        switch(sectionId) {
            case 'browse':
                document.getElementById('browse-section').classList.add('active');
                title = 'Browse Books';
                renderBooks();
                break;
            case 'borrowed':
                document.getElementById('borrowed-section').classList.add('active');
                title = 'My Borrowed Books';
                renderBorrowedBooks();
                break;
            case 'history':
                document.getElementById('history-section').classList.add('active');
                title = 'Borrowing History';
                renderHistory();
                break;
            case 'profile':
                document.getElementById('profile-section').classList.add('active');
                title = 'My Profile';
                break;
            default:
                document.getElementById('browse-section').classList.add('active');
                title = 'Browse Books';
        }

        document.querySelector('.top-bar h1').textContent = title;
    }

    // Render books grid
    function renderBooks(booksToRender = books) {
        booksGrid.innerHTML = '';

        if (booksToRender.length === 0) {
            booksGrid.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-book-open"></i>
                    <p>No books found matching your criteria.</p>
                </div>
            `;
            return;
        }

        booksToRender.forEach(book => {
            const bookCard = document.createElement('div');
            bookCard.className = 'book-card';
            
            const statusClass = book.status === 'Available' ? 'status-available' : 'status-borrowed';
            
            bookCard.innerHTML = `
                <div class="book-cover">
                    <img src="${book.cover}" alt="${book.title} cover">
                    <span class="book-status ${statusClass}">${book.status}</span>
                </div>
                <div class="book-info">
                    <h3>${book.title}</h3>
                    <p class="book-author">${book.author}</p>
                    <p class="book-genre">${book.genre}</p>
                </div>
                <button class="view-details-btn" data-id="${book.id}">
                    View Details
                </button>
            `;

            booksGrid.appendChild(bookCard);

            // Add click event for view details button
            bookCard.querySelector('.view-details-btn').addEventListener('click', () => {
                openBookDetails(book.id);
            });
        });
    }

    // Render borrowed books table
    function renderBorrowedBooks() {
        const borrowedTableBody = document.getElementById('borrowedTableBody');
        borrowedTableBody.innerHTML = '';

        if (borrowedBooks.length === 0) {
            borrowedTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="no-data">
                        <i class="fas fa-book"></i>
                        <p>You haven't borrowed any books yet.</p>
                    </td>
                </tr>
            `;
            return;
        }

        borrowedBooks.forEach(book => {
            const row = document.createElement('tr');
            const statusClass = book.status === 'Overdue' ? 'text-danger' : 'text-success';
            
            row.innerHTML = `
                <td><img src="${book.cover}" alt="${book.title} cover"></td>
                <td>${book.title}</td>
                <td>${book.borrowDate}</td>
                <td>${book.dueDate}</td>
                <td><span class="${statusClass}">${book.status}</span></td>
                <td>
                    <button class="return-btn" data-id="${book.id}">
                        <i class="fas fa-undo"></i> Return
                    </button>
                </td>
            `;

            borrowedTableBody.appendChild(row);

            // Add return button event listener
            row.querySelector('.return-btn').addEventListener('click', () => {
                handleReturn(book.id);
            });
        });
    }

    // Render borrowing history
    function renderHistory() {
        const historyTableBody = document.getElementById('historyTableBody');
        historyTableBody.innerHTML = '';

        if (borrowHistory.length === 0) {
            historyTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="no-data">
                        <i class="fas fa-history"></i>
                        <p>No borrowing history yet.</p>
                    </td>
                </tr>
            `;
            return;
        }

        borrowHistory.forEach(book => {
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td><img src="${book.cover}" alt="${book.title} cover"></td>
                <td>${book.title}</td>
                <td>${book.borrowDate}</td>
                <td>${book.returnDate}</td>
                <td><span class="text-success">${book.status}</span></td>
            `;

            historyTableBody.appendChild(row);
        });
    }

    // Open book details modal
    function openBookDetails(bookId) {
        const book = books.find(b => b.id === bookId);
        if (!book) return;

        const bookInfo = document.querySelector('.book-info');
        bookInfo.innerHTML = `
            <div class="book-detail-grid">
                <div class="book-detail-cover">
                    <img src="${book.cover}" alt="${book.title} cover">
                </div>
                <div class="book-detail-info">
                    <h3>${book.title}</h3>
                    <p class="book-author">by ${book.author}</p>
                    <p class="book-genre"><i class="fas fa-bookmark"></i> ${book.genre}</p>
                    <p class="book-status ${book.status === 'Available' ? 'text-success' : 'text-warning'}">
                        <i class="fas fa-circle"></i> ${book.status}
                    </p>
                    <div class="book-metadata">
                        <p><strong>ISBN:</strong> ${book.isbn}</p>
                        <p><strong>Published:</strong> ${book.published}</p>
                        <p><strong>Publisher:</strong> ${book.publisher}</p>
                    </div>
                    <p class="book-description">${book.description}</p>
                </div>
            </div>
        `;

        // Update borrow button state
        borrowBtn.disabled = book.status !== 'Available';
        borrowBtn.setAttribute('data-id', book.id);

        bookModal.style.display = 'block';
    }

    // Close modal
    function closeModal() {
        bookModal.style.display = 'none';
    }

    // Handle book borrowing
    function handleBorrow() {
        const bookId = parseInt(borrowBtn.getAttribute('data-id'));
        const book = books.find(b => b.id === bookId);
        
        if (!book || book.status !== 'Available') return;

        // Update book status
        book.status = 'Borrowed';
        
        // Add to borrowed books
        const today = new Date();
        const dueDate = new Date();
        dueDate.setDate(today.getDate() + 30); // 30 days borrowing period

        const borrowedBook = {
            id: borrowedBooks.length + 1,
            title: book.title,
            author: book.author,
            cover: book.cover,
            borrowDate: today.toISOString().split('T')[0],
            dueDate: dueDate.toISOString().split('T')[0],
            status: 'On Time'
        };

        borrowedBooks.push(borrowedBook);

        // Close modal and refresh books display
        closeModal();
        renderBooks();
        alert('Book borrowed successfully! Please check "My Borrowed" section for details.');
    }

    // Handle book return
    function handleReturn(borrowId) {
        const isConfirmed = confirm('Are you sure you want to return this book?');
        if (!isConfirmed) return;

        const borrowedBook = borrowedBooks.find(b => b.id === borrowId);
        if (!borrowedBook) return;

        // Update book status in main books array
        const book = books.find(b => b.title === borrowedBook.title);
        if (book) {
            book.status = 'Available';
        }

        // Add to history
        borrowHistory.push({
            ...borrowedBook,
            returnDate: new Date().toISOString().split('T')[0],
            status: 'Returned'
        });

        // Remove from borrowed books
        borrowedBooks = borrowedBooks.filter(b => b.id !== borrowId);

        // Refresh displays
        renderBorrowedBooks();
        renderBooks();
        alert('Book returned successfully!');
    }

    // Search and filter functionality
    function handleSearch() {
        applyFilters();
    }

    function handleFilters() {
        applyFilters();
    }

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedGenre = genreFilter.value;
        const selectedAvailability = availabilityFilter.value;

        const filteredBooks = books.filter(book => {
            const matchesSearch = 
                book.title.toLowerCase().includes(searchTerm) ||
                book.author.toLowerCase().includes(searchTerm) ||
                book.genre.toLowerCase().includes(searchTerm);

            const matchesGenre = !selectedGenre || book.genre === selectedGenre;
            const matchesAvailability = !selectedAvailability || book.status === selectedAvailability;

            return matchesSearch && matchesGenre && matchesAvailability;
        });

        renderBooks(filteredBooks);
    }

    // Handle logout
    function handleLogout() {
        const isConfirmed = confirm('Are you sure you want to logout?');
        if (isConfirmed) {
            window.location.href = 'index.html';
        }
    }

    // Initialize the dashboard
    init();
});