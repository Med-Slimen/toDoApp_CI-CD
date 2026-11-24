const form = document.getElementById('task-form');
const input = document.getElementById('title');
const list = document.getElementById('task-list');
const empty = document.getElementById('empty');
const message = document.getElementById('message');
const versionEl = document.getElementById('version');

function showMessage(text) {
  message.textContent = text;
}

// Appelle le backend et renvoie le JSON, ou lève une erreur lisible.
async function api(path, options = {}) {
  const response = await fetch(path, options);
  if (!response.ok) {
    let reason = 'Erreur ' + response.status;
    try {
      const data = await response.json();
      if (data.error) reason = data.error;
    } catch (_) { /* pas de JSON: on garde le message par défaut */ }
    throw new Error(reason);
  }
  return response.json();
}

// Construit une ligne <li> pour une tâche.
function renderTask(task) {
  const li = document.createElement('li');
  li.className = 'task-item' + (task.done ? ' done' : '');

  const checkbox = document.createElement('input');
  checkbox.type = 'checkbox';
  checkbox.checked = task.done;
  checkbox.setAttribute('aria-label', 'Marquer comme faite : ' + task.title);
  checkbox.addEventListener('change', () => toggleTask(task.id));

  const title = document.createElement('span');
  title.className = 'task-title';
  title.textContent = task.title;

  const del = document.createElement('button');
  del.type = 'button';
  del.className = 'delete-btn';
  del.textContent = 'Supprimer';
  del.addEventListener('click', () => deleteTask(task.id));

  li.append(checkbox, title, del);
  return li;
}

function renderTasks(tasks) {
  list.replaceChildren(...tasks.map(renderTask));
  empty.hidden = tasks.length > 0;
}

async function loadTasks() {
  try {
    renderTasks(await api('/tasks'));
    showMessage('');
  } catch (e) {
    showMessage('Impossible de charger les tâches : ' + e.message);
  }
}

async function toggleTask(id) {
  try {
    await api('/tasks/' + id + '/toggle', { method: 'POST' });
  } catch (e) {
    showMessage('Impossible de modifier la tâche : ' + e.message);
  }
  await loadTasks();
}

async function deleteTask(id) {
  try {
    await api('/tasks/' + id, { method: 'DELETE' });
  } catch (e) {
    showMessage('Impossible de supprimer la tâche : ' + e.message);
  }
  await loadTasks();
}

form.addEventListener('submit', async (event) => {
  event.preventDefault(); // empêche le rechargement de la page
  const title = input.value.trim();
  if (!title) {
    showMessage('Le titre ne peut pas être vide.');
    return;
  }
  try {
    await api('/tasks', { method: 'POST', body: new URLSearchParams({ title }) });
    input.value = '';
    showMessage('');
    await loadTasks();
    input.focus();
  } catch (e) {
    showMessage("Impossible d'ajouter la tâche : " + e.message);
  }
});

// Affiche la version déployée (lue depuis /health).
async function loadVersion() {
  try {
    const data = await api('/health');
    versionEl.textContent = data.version;
  } catch (_) {
    versionEl.textContent = 'inconnue';
  }
}

loadVersion();
loadTasks();