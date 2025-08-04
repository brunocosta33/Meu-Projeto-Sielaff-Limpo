<style>
    .repeat-toggle-wrapper {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 2rem;
        padding: 0.5rem;
        display: flex;
        justify-content: center;
        max-width: 420px;
        margin-bottom: 1rem;
    }

    .repeat-toggle-wrapper label {
        flex: 1;
        text-align: center;
        padding: 0.5rem 1rem;
        margin: 0;
        border-radius: 2rem;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.2s, color 0.2s;
    }

    .repeat-toggle-wrapper input[type="radio"] {
        display: none;
    }

    .repeat-toggle-wrapper input[type="radio"]:checked + label {
        background-color: #0d6efd;
        color: #fff;
    }
</style>

<div class="form-group">
    <div class="repeat-toggle-wrapper mx-auto">
        <input type="radio" name="repetir" id="no_repeat" value="0" onclick="showNoRepeat()" checked>
        <label for="no_repeat">Não Repete</label>

        <input type="radio" name="repetir" id="repeat" value="1" onclick="showRepeat()">
        <label for="repeat">Repetir</label>
    </div>

    <div id="no_repeat_div" class="mt-4">
        <div class="d-flex flex-wrap gap-2 text-center">
            <div class="form-group flex-fill">
                <label for="data_limite" class="mb-2"><strong>Data Limite</strong></label>
                <input type="date" name="data_limite" id="data_limite" min="{{ now()->toDateString() }}" class="form-control">
            </div>
            <div class="form-group flex-fill">
                <label for="hora_limite" class="mb-2"><strong>Hora Limite</strong></label>
                <input type="time" name="hora_limite" id="hora_limite" class="form-control">
            </div>
        </div>
    </div>

    <div id="repeat_div" class="mt-4" style="display: none;">
        <div class="d-flex flex-wrap gap-2 text-center">
            <div class="form-group flex-fill">
                <label for="initial_date" class="mb-2"><strong>Início</strong></label>
                <input type="date" name="initial_date" id="initial_date" min="{{ now()->toDateString() }}" class="form-control">
            </div>
            <div class="form-group flex-fill">
                <label for="final_date" class="mb-2"><strong>Fim</strong></label>
                <input type="date" name="final_date" id="final_date" min="{{ now()->toDateString() }}" class="form-control">
            </div>
            <div class="form-group flex-fill">
                <label for="time" class="mb-2"><strong>Horário</strong></label>
                <input type="time" name="time" id="time" class="form-control">
            </div>
        </div>

        <div class="form-group mt-3">
            <label for="period" class="mb-2"><strong>Repetir a cada</strong></label>
            <select name="period" id="period" class="form-select" onchange="showDaysOfWeek('daysOfWeek', this)">
                <option value="">Selecionar</option>
                <option value="day">Dia</option>
                <option value="week">Semana</option>
                <option value="month">Mês</option>
                <option value="year">Ano</option>
            </select>
        </div>

        <div class="form-group mt-3" id="daysOfWeek" style="display: none;">
            <div class="d-flex flex-wrap gap-3">
                @foreach(['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $i => $day)
                    <div class="form-check">
                        <input type="checkbox" name="days_of_week[]" id="{{ $i }}" value="{{ $i }}" class="form-check-input">
                        <label for="{{ $i }}" class="form-check-label"><strong>{{ $day }}</strong></label>
                    </div>
                @endforeach
            </div>
            <div class="mt-3">
                <small>*Se deixado em branco, todos os dias serão considerados.</small>
            </div>
        </div>
    </div>
</div>

<script>
    function showNoRepeat() {
        document.getElementById("no_repeat_div").style.display = "";
        document.getElementById("repeat_div").style.display = "none";

        document.getElementById('data_limite').required = true;
        document.getElementById('hora_limite').required = true;

        document.getElementById('initial_date').required = false;
        document.getElementById('final_date').required = false;
        document.getElementById('time').required = false;
        document.getElementById('period').required = false;
    }

    function showRepeat() {
        document.getElementById("no_repeat_div").style.display = "none";
        document.getElementById("repeat_div").style.display = "";

        document.getElementById('data_limite').required = false;
        document.getElementById('hora_limite').required = false;

        document.getElementById('initial_date').required = true;
        document.getElementById('final_date').required = true;
        document.getElementById('time').required = true;
        document.getElementById('period').required = true;
    }

    function showDaysOfWeek(divId, element) {
        document.getElementById(divId).style.display = element.value === "week" ? 'block' : 'none';
    }

    window.addEventListener('DOMContentLoaded', () => {
        const noRepeat = document.getElementById('no_repeat');
        const repeat = document.getElementById('repeat');

        if (noRepeat && noRepeat.checked) {
            showNoRepeat();
        } else if (repeat && repeat.checked) {
            showRepeat();
        }
    });
</script>
