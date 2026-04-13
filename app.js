let currentWords = [] // array loaded from server

// loading page
window.onload = async () => {
    await loadAndDisplayNotes() // load strings from server
    promptUserForAuth() // auth user
}

function clearFields() {
    document.querySelectorAll('textarea').forEach(input => {
        input.value = ''
    })
}

// show mssg
function showMessage(message) {
    document.getElementById('message').innerHTML = message
}

// Get the code and save the code
function promptUserForAuth() {
    let code = window.prompt("Enter the code:")
    document.getElementById('auth-code').value = code || ''
}

// load strings from server
async function loadAndDisplayNotes() {
    try {
        const response = await fetch('app.php')
        const data = await response.json()
        currentWords = data.words
        renderNotesList(currentWords)
    } catch (err) { console.error(err) }
}

// Render list of string from server at html page
function renderNotesList(words) {
    const container = document.getElementById('words-list')
    container.innerHTML = '' // clear old output

    words.forEach(word => {
        const itemDiv = document.createElement('div')
        itemDiv.className = 'note-item'
        itemDiv.innerHTML = ` ${word.EnglishWord} | ${word.SpanishWord} | ${word.RussianWord}
        <span class="delete-btn" onclick="removeNote('${word.EnglishWord}')"> X</span> `
        container.appendChild(itemDiv)
    })
}

// Delete some string from json and html
async function removeNote(EnglishWord) {
    const authCode = document.getElementById('auth-code').value
    fetch('app.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_note', EnglishWord: EnglishWord, p: authCode }),
    }).then(response => response.text()).then(data => {
        showMessage(data)
        loadAndDisplayNotes() // reload list
    }).catch(console.error)
}

// Save new string
async function saveNote() {
    const authCode = document.getElementById('auth-code').value
    const EnglishWord = document.getElementById('EnglishWord').value.trim()
    const SpanishWord = document.getElementById('SpanishWord').value.trim()
    const RussianWord = document.getElementById('RussianWord').value.trim()
    if (!EnglishWord || !SpanishWord || !RussianWord) return alert('Empty note!')

    fetch('app.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add_word', 
            EnglishWord: EnglishWord, SpanishWord: SpanishWord, RussianWord: RussianWord, 
            p: authCode }),
    }).then(response => response.text()).then(data => {
        showMessage(data)
        loadAndDisplayNotes() // reload list
    }).catch(console.error)
}