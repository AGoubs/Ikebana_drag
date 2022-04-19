let max_time = 8;
let time = 0;

let progress = document.getElementById('progress-bar');
let time_counter = document.getElementById('time_counter');
let max_time_counter = document.getElementById('max_time');

const droppable = new Draggable.Droppable(document.querySelectorAll('.drag_container'), {
  draggable: '.item',
  dropzone: '.drag_dropzone'
});

document.addEventListener("DOMContentLoaded", function () {
  let table = document.getElementById('timetable');
  let table_cells = table.rows[1].cells;
  for (let i = 0; i < table_cells.length; i++) {
    const element = table_cells[i];
    //Si un td contient une activité
    if (document.getElementById(element.id).innerHTML != "") {
      for (let y = 0; y < document.getElementById(element.id).children.length; y++) {
        const input_id = document.getElementById(element.id).children[y].id;

        //On créer les inputs pour les activités déjà existante
        var stats = document.getElementById('stats');
        var x = document.createElement("INPUT");
        x.setAttribute("type", "hidden");
        x.setAttribute("id", "input-" + input_id);
        x.setAttribute("value", element.id)
        stats.appendChild(x);

        increaseBar(element.id);

        deleteExistingActivity(input_id);
      }

    }
  }
});


droppable.on('droppable:stop', (evt) => {

  //Si on ajoute dans le tableau
  if (evt.dropzone.id != "task_list_dropzone") {

    if (!document.getElementById("input-" + evt.dragEvent.data.originalSource.id)) {

      let dragged_row_cells = evt.dropzone.parentNode.cells
      for (let i = 0; i < dragged_row_cells.length; i++) {
        const element = dragged_row_cells[i];
        if (element.className.includes("draggable-dropzone--occupied")) {

          element.classList.add("occupied-zone");
          element.classList.remove("draggable-dropzone--occupied");
        }
      }

      var stats = document.getElementById('stats');
      var x = document.createElement("INPUT");
      x.setAttribute("type", "hidden");
      x.setAttribute("id", "input-" + evt.dragEvent.data.originalSource.id);
      x.setAttribute("value", evt.dropzone.id)
      stats.appendChild(x);

      increaseBar(evt.dropzone.id);
    }
    else {
      let original_input = document.getElementById("input-" + evt.dragEvent.data.originalSource.id);
      decreaseBar(original_input.value);
      original_input.setAttribute("value", evt.dropzone.id)
      increaseBar(evt.dropzone.id);
    }
  }
  //Si on enlève du tableau
  else {
    if (document.getElementById("input-" + evt.dragEvent.data.originalSource.id)) {
      var input = document.getElementById("input-" + evt.dragEvent.data.originalSource.id);
      decreaseBar(input.value);
      input.parentNode.removeChild(input);
    }
  }

});

function addtime() {
  max_time++;
  max_time_counter.textContent = max_time;

  increaseBar(0)
}

function removetime() {
  max_time--;
  max_time_counter.textContent = max_time;

  increaseBar(0)
}

function increaseBar(hour) {
  time += parseInt(hour);
  time_counter.textContent = time;
  progress.style.width = (time * 100 / max_time) + '%';
}

function decreaseBar(hour) {
  time -= parseInt(hour);
  time_counter.textContent = time;
  progress.style.width = (time * 100 / max_time) + '%';
}

function deleteExistingActivity(input_id) {
  let li = document.getElementById('task_list').children;
  for (let i = 0; i < li.length; i++) {
    const element = li[i].children[0];
    if (element && element.id == input_id) {
      element.parentNode.removeChild(element);
    }
  }
}