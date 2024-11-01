<?php
session_start();
require 'db.php'; // Include your database connection

// Initialize chat history if not already set
if (!isset($_SESSION['chatMessages'])) {
    $_SESSION['chatMessages'] = [];
}

// Set default chat ID to 1 if not already set
if (!isset($_SESSION['chatId'])) {
    $_SESSION['chatId'] = 1;
    $_SESSION['chatMessages'][1] = []; // Initialize messages for Chat 1
}

// Load messages for the current chat from the database
$currentChatId = isset($_POST['chatId']) ? (int)$_POST['chatId'] : $_SESSION['chatId'];
$_SESSION['chatId'] = $currentChatId; // Update session chat ID

$stmt = $pdo->prepare("SELECT datetime, message, type FROM public.messages WHERE session_id = ? ORDER BY datetime");
$stmt->execute([$currentChatId]);
$allMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Handle the "New Chat" button click
if (isset($_POST['newChat'])) {
    $currentChatId = count($_SESSION['chatMessages']) + 1; // Increment for a new chat ID
    $_SESSION['chatId'] = $currentChatId;
    $_SESSION['chatMessages'][$currentChatId] = []; // Initialize new chat messages
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIDAS by Cyber Beast Tech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="icon" href="images/robot avatars, robot, avatar, robotics, i.png" type="image/png">
    <style>
        body {
            display: flex;
            justify-content: flex-start;
            align-items: stretch;
            min-height: 100vh;
            margin: 0;
            background-color: black;
            color: white;
        }
        #chat-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: left;
            background: black;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            width: 95%;
            height: calc(100vh - 350px); /* Increased height of chat container */
            margin: 10px auto;
            position: relative;
        }
        #chat-window {
            width: 100%;
            height: 100%;
            padding: 10px;
            margin-top: 5px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #339b39 #000000;
            scroll-behavior: smooth;
            display: flex;
            flex-direction: column-reverse;
        }
        #chat-window::-webkit-scrollbar {
            width: 3px;
        }
        #chat-window::-webkit-scrollbar-track {
            background: #000000;
        }
        #chat-window::-webkit-scrollbar-thumb {
            background: #339b39;
        }
        #chat-window::-webkit-scrollbar-thumb:hover {
            background: #2a7e2f;
        }
        input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #339b39;
            margin-top: 10px;
            height: 40px;
            box-sizing: border-box;
            vertical-align: middle;
        }
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            background-color: #00FF00;
            color: #D3D3D3;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #00FF00;
        }
        .input-group {
            background-color: black;
            border-radius: 10px;
            display: flex;
            align-items: center;
            position: fixed;
            bottom: 80px; /* Moved up */
            left: calc(55% + 50px); /* Moved right by half of sidebar width */
            transform: translateX(-50%);
            width: 90%;
            max-width: 80%;
        }
        .form-control {
            background-color: black;
            color: grey;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            flex: 1;
            margin-right: 0;
            height: 50px;
        }
        .form-control:focus {
            background-color: black;
            color: grey;
            outline: none;
            box-shadow: 0 0 0 2px #339b39;
        }
        .circle-button {
            background-color: #339b39;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            margin-left: 10px;
            transition: box-shadow 0.3s ease;
        }
        .circle-button:hover {
            background-color: #00FF00;
            box-shadow: 0 0 10px #00FF00;
        }
        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            margin-right: 10px;
        }
        .file-upload-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }
        .file-upload-btn {
            background-color: #339b39;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: box-shadow 0.3s ease;
        }
        .file-upload-btn:hover {
            background-color: #00FF00;
            box-shadow: 0 0 10px #00FF00;
        }
        @media (max-width: 600px) {
            .form-control {
                padding: 8px;
            }
            .circle-button, .file-upload-btn {
                width: 35px;
                height: 35px;
            }
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        #messages {
            height: 100%;
            overflow-y: auto;
            padding: 10px;
        }
        .message {
            margin: 5px 0;
            text-align: left;
        }
        .blue {
            color: #00FF00;
        }
        .btn-green {
            border-color: green;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-green img {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            filter: brightness(0) invert(1);
        }
        .custom-radio-card {
            display: block;
            cursor: pointer;
            margin-bottom: 5px;
        }
        .custom-radio-card input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }
        .message.user-message {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            background-color: #2c2c2c;
            color: #ffffff;
            width: 90%;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .message.ai-message {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            background-color: #1e1e1e;
            color: #ffffff;
            width: 90%;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .custom-radio-card input:checked + .btn-green,
        .custom-radio-card .btn-green:hover {
            background-color: #00FF00;
            color: black;
        }
        .disclaimer-text {
            color: grey;
            font-size: 12px;
            text-align: center;
            margin-top: 5px;
            position: fixed;
            bottom: 30px;
            left: 58%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 80%;
        }
        .pseudo-ide {
            background-color: #2d2d2d;
            color: #f8f8f2;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-family: 'Courier New', Courier, monospace;
        }
        .pseudo-ide .language-label {
            background-color: #44475a;
            color: #f8f8f2;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 5px;
        }
        .pseudo-ide pre {
            margin: 0;
        }
        #selected-bot {
            font-size: 14px;
            color: #888;
            margin-left: 10px;
            align-self: center;
        }
		
		#sidebar {
            width: 250px;
            background-color: #1c1c1c;
            padding: 15px;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        #main-content {
            flex-grow: 1;
            padding: 20px;
            padding-bottom: 200px; /* Increased padding */
        }
        
        #initial-text {
            text-align: center;
            width: 100%;
            margin-top: 20px;
        }
        
        #chat-window {
            width: 95%;
            max-width: 1100px;
            margin: 0 auto;
        }
        .bot-selection {
            position: fixed;
            bottom: 120px; /* Moved closer to input group */
            left: calc(55% + 50px); /* Moved slightly to the left */
            transform: translateX(-50%);
            width: 90%;
            max-width: 80%;
            display: flex;
            justify-content: flex-start;
            background-color: black;
            padding: 10px 0;
        }
        .custom-radio-card .btn-green {
            padding: 5px 10px;
            font-size: 0.9em;
        }
        .custom-radio-card .btn-green img {
            width: 15px;
            height: 15px;
        }
        /* New style for sticky logo */
        .sticky-logo {
            position: sticky;
            top: 0;
            background-color: black;
            z-index: 1000;
            padding: 10px 0;
        }
        #starting-text {
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }
        #starting-text.fade-out {
            opacity: 0;
        }
        #chat-messages {
            display: none;
        }
        #chat-messages.show {
            display: block;
        }
		#sidebar .btn {
            text-align: left;
            padding: 10px 15px;
        }
        #sidebar .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        #sidebar .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        #sidebar .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        #sidebar .btn:hover {
            opacity: 0.9;
        }
        #sidebar .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }

        #sidebar .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
        .sidebar-content {
            flex-grow: 1;
            overflow-y: auto;
        }
        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
        }
    </style>
</head>
<body>
	<div id="sidebar">
		<div class="sidebar-content">
			<h4 class="text-center">User History</h4>
			<div id="chatHistory" class="chat-history">
				<?php
				// Fetch all chat sessions for history
				$stmt = $pdo->query("SELECT DISTINCT session_id FROM public.messages");
				$chatIds = []; // Array to store chat IDs for display
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$chatIds[] = $row['session_id'];
				}

				// Add current session ID to the array
				if (!in_array($currentChatId, $chatIds)) {
					$chatIds[] = $currentChatId;
				}

				// Display all chat sessions as cards
				foreach ($chatIds as $chatId): ?>
					<div class="card mb-2" onclick="loadChatMessages(<?= $chatId ?>)">
						<div class="card-body">
							<h5 style="color:black;" class="card-title">Chat Information</h5>
							<?echo $allMessages[$chatId]; ?>
							<p style="color:black;" class="card-text">Session ID:<?= $chatId ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="sidebar-footer">
			<h5 style="color:white;" class="card-title mb-3">Navigation</h5>
			<a href="chathistory.php" class="btn btn-primary mb-2 w-100">View Chat History</a>
			<a href="adminpanel" class="btn btn-secondary mb-2 w-100">Admin Panel</a>
			<form method="POST">
				<button type="submit" name="newChat" class="btn btn-primary mb-2 w-100">New Chat</button>
			</form>
			<a href="edit-bot.php?botname=cbt4o" class="btn btn-info w-100">Edit Bot</a>
		</div>
	</div>
    <div id="main-content">
        <div class="container-fluid my-3 sticky-logo">
            <div class="row justify-content-center">
                <div class="col-12 d-flex flex-column align-items-center">
                    <img src="images/MIDAS3.png" width="250px" max-width="250px" alt="MIDAS Logo">
                </div>
            </div>
        </div>
        <div id="chat-container">
            <div id="chat-window">
                <div id="messages">
					<?php foreach ($allMessages as $msg):
						if ($msg['type'] == 'input') {
							echo '<div class="message user-message">';
								echo '<span style="font-size:12px;color: #FF00FF;">User</span>';
								echo '<br>'.htmlspecialchars($msg['message']);
							echo '</div>';
						} elseif ($msg['type'] == 'output') {
							echo '<div class="message ai-message">';
								echo '<span style="font-size:12px;color: #00FF00;">MIDAS</span>';
								echo '<br>'.htmlspecialchars($msg['message']);
							echo '</div>';
						}
					endforeach; ?>
				</div>
            </div>
        </div>
        <div id="message" class="mt-2"></div>
    </div>
    
    <div class="bot-selection">
        <label class="custom-radio-card">
            <input type="radio" name="option" value="cbt4o">
            <div class="d-flex align-items-center btn btn-green m-2">
                <img src="images/robot avatars, robot, avatar, robotics, i.png" width="12px" alt="Button 1 Icon">
                <div class="card-body">
                    CBT4o
                </div>
            </div>
        </label>
        <label class="custom-radio-card">
            <input type="radio" name="option" value="cbt4">
            <div class="d-flex align-items-center btn btn-green m-2">
                <img src="images/robot avatars, robot, avatar, robotics, e.png" width="12px" alt="Button 1 Icon">
                <div class="card-body">
                    CBT4
                </div>
            </div>
        </label>
        <label class="custom-radio-card">
            <input type="radio" name="option" value="cbt35">
            <div class="d-flex align-items-center btn btn-green m-2">
                <img src="images/robot avatars, robot, avatar, robotics, f.png" width="12px" alt="Button 1 Icon">
                <div class="card-body">
                    CBT3.5
                </div>
            </div>
        </label>
        <label class="custom-radio-card">
            <input type="radio" name="option" value="codegen">
            <div class="d-flex align-items-center btn btn-green m-2">
                <img src="images/robot avatars, robot, avatar, robotics, k.png" width="12px" alt="Button 1 Icon">
                <div class="card-body">
                    Code Gen
                </div>
            </div>
        </label>
        <p id="selected-bot">Currently Selected: CBT4o</p>
    </div>
    
    <div class="input-group">
        <div class="file-upload-wrapper">
            <button class="circle-button file-upload-btn">
                <span class="material-symbols-outlined">attach_file</span>
            </button>
            <input type="file" id="pdfUpload" accept="application/pdf" />
        </div>
        <input type="text" class="form-control" id="user-input" placeholder="Type your message here...">
        <button class="circle-button" id="send-button">
            <span class="material-symbols-outlined">arrow_forward</span>
        </button>
    </div>
    <p class="disclaimer-text">LLMs may make mistakes, verify the information provided closely.</p>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
	<script src="config.js"></script>

	<script>
		async function fetchChatInfo() {
			try {
				const response = await fetch('fetch_chat_info.php');
				const data = await response.json();
				
				if (data.error) {
					document.getElementById('chat-info').innerText = data.error;
				} else {
					// Clear any existing chat info
					document.getElementById('chat-info').innerHTML = '';

					// Check if data is an array and has sessions
					if (Array.isArray(data) && data.length > 0) {
						const uniqueSessions = new Set(); // To track unique session IDs
						const filteredData = data.filter(chat => {
							if (!uniqueSessions.has(chat.session_id)) {
								uniqueSessions.add(chat.session_id);
								return true; // Keep this session
							}
							return false; // Skip duplicate session
						});
						
						// Display filtered chat sessions
						filteredData.forEach(chat => {
							const chatCard = document.createElement('div');
							chatCard.className = 'chat-card';
							chatCard.innerHTML = `Date and Time: ${chat.datetime}<br>Session ID: ${chat.session_id}<br><br>`;
							document.getElementById('chat-info').appendChild(chatCard);
						});
					} else {
						document.getElementById('chat-info').innerText = 'No chat sessions available.';
					}
				}
			} catch (error) {
				document.getElementById('chat-info').innerText = 'Error fetching chat information.';
			}
		}

		// Fetch chat information when the page loads
		window.onload = fetchChatInfo;
	</script>
	<script>
		document.getElementById('pdfUpload').addEventListener('change', async function() {
			const fileInput = document.getElementById('pdfUpload');
			const messageDiv = document.getElementById('message');
			
			if (fileInput.files.length === 0) {
				messageDiv.textContent = 'Please select a PDF file to upload.';
				return;
			}

			const file = fileInput.files[0];

			if (file.type === 'application/pdf') {
				const formData = new FormData();
				formData.append('file', file);

				try {
					const response = await fetch('http://localhost:8000/upload', {
						method: 'POST',
						body: formData
					});

					if (response.ok) {
						const result = await response.json();
						messageDiv.textContent = `Upload successful: ${result.message}`;
					} else {
						messageDiv.textContent = 'Upload failed. Please try again.';
					}
				} catch (error) {
					messageDiv.textContent = 'Error occurred while uploading: ' + error.message;
				}
			} else {
				messageDiv.textContent = 'The selected file is not a PDF.';
			}
		});
	</script>
	<!--
	<script>
		document.getElementById('new-chat-btn').addEventListener('click', function() {
			// Clear the chat window
			document.getElementById('messages').innerHTML = '';
			
			// Reset the starting message
			const startingMessage = document.createElement('div');
			startingMessage.id = 'starting-message';
			startingMessage.className = 'message ai-message';
			startingMessage.innerHTML = '<p>Start a conversation and explore the power of AI.<br> Your chat history will be displayed here.</p>';
			document.getElementById('messages').appendChild(startingMessage);
			
			// Clear the user input
			document.getElementById('user-input').value = '';
			
			// You may want to reset other states or send a request to the server to start a new session
		});
	</script>
	-->
	
	<script>
		function loadChatMessages(chatId) {
			const form = document.createElement('form');
			form.method = 'POST';
			form.action = '';
			const input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'chatId';
			input.value = chatId;
			form.appendChild(input);
			document.body.appendChild(form);
			form.submit();
		}
	</script>
</body>
</html>
