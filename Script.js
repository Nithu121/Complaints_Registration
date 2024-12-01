document.getElementById('loginForm').addEventListener('submit', login);
document.getElementById('registerForm').addEventListener('submit', register);
document.getElementById('submitComplaint').addEventListener('click', submitComplaint);

function login(event) {
    event.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // Send login request to server
    fetch('login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password })
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_admin) {
                document.getElementById('adminSection').style.display = 'block';
                fetchAdminComplaints();
            } else {
                document.getElementById('complaintSection').style.display = 'block';
                fetchUserComplaints(data.user_id);
            }
        } else {
            alert('Invalid login credentials');
        }
    });
}

function register(event) {
    event.preventDefault();
    const username = document.getElementById('newUsername').value;
    const password = document.getElementById('newPassword').value;
    const email = document.getElementById('email').value;

    // Send registration request to server
    fetch('register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password, email })
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Registration successful');
        } else {
            alert('Registration failed');
        }
    });
}

function submitComplaint() {
    const subject = document.getElementById('complaintSubject').value;
    const description = document.getElementById('complaintDescription').value;

    // Send complaint to server
    fetch('submit_complaint.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ subject, description })
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Complaint submitted successfully');
            fetchUserComplaints(data.user_id);
        } else {
            alert('Failed to submit complaint');
        }
    });
}

function fetchUserComplaints(user_id) {
    fetch(`fetch_user_complaints.php?user_id=${user_id}`)
    .then(response => response.json())
    .then(data => {
        const complaintsDiv = document.getElementById('userComplaints');
        complaintsDiv.innerHTML = data.complaints.map(complaint => `
            <div>
                <h3>${complaint.subject}</h3>
                <p>${complaint.description}</p>
                <p>Status: ${complaint.status}</p>
            </div>
        `).join('');
    });
}

function fetchAdminComplaints() {
    fetch('fetch_admin_complaints.php')
    .then(response => response.json())
    .then(data => {
        const complaintsDiv = document.getElementById('adminComplaints');
        complaintsDiv.innerHTML = data.complaints.map(complaint => `
            <div>
                <h3>${complaint.subject}</h3>
                <p>${complaint.description}</p>
                <p>Status: ${complaint.status}</p>
                <button onclick="resolveComplaint(${complaint.id})">Resolve</button>
            </div>
        `).join('');
    });
}

function resolveComplaint(complaint_id) {
    fetch('resolve_complaint.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ complaint_id })
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchAdminComplaints();
        } else {
            alert('Failed to resolve complaint');
        }
    });
}
