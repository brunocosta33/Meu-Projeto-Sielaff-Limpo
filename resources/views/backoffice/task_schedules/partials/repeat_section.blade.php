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
    {{-- Toggle entre não repetir e repetir --}}
    <div class="repeat-toggle-wrapper mx-auto">
        <input type="radio" name="repetir" id="no_repeat" value="0" onclick="showNoRepeat()" checked>
        <label for="no_repeat">{{ __('Não Repete') }}</label>

        <input type="radio" name="repetir" id="repeat" value="1" onclick="showRepeat()">
        <label for="repeat">{{ __('Repetir') }}</label>
    </div>

    {{-- Opção: Não repete --}}
    <div id="no_repeat_div" class="mt-4">
        <div class="d-flex flex-wrap gap-2 text-center">
            <div class="form-group flex-fill">
                <label for="data_limite" class="mb-2"><strong>{{ __('Data Limite') }}</strong></label>
                <input type="date" name="data_limite" id="data_limite" min="{{ now()->toDateString() }}" class="form-control">
            </div>
            <div class="form-group flex-fill">
                <label for="hora_limite" class="mb-2"><strong>{{ __('Hora Limite') }}</strong></label>
                <input type="time" name="hora_limite" id="hora_limite" class="form-control">
            </div>
        </div>
    </div>

    {{-- Opção: Repetir --}}
    <div id="repeat_div" class="mt-4" style="display: none;">
        <div class="d-flex flex-wrap gap-2 text-center">
            <div class="form-group flex-fill">
                <label for="initial_date" class="mb-2"><strong>{{ __('Início') }}</strong></label>
                <input type="date" name="initial_date" id="initial_date" min="{{ now()->toDateString() }}" class="form-control">
            </div>
            <div class="form-group flex-fill">
                <label for="final_date" class="mb-2"><strong>{{ __('Fim') }}</strong></label>
                <input type="date" name="final_date" id="final_date" min="{{ now()->toDateString() }}" class="form-control">
            </div>
            <div class="form-group flex-fill">
                <label for="time" class="mb-2"><strong>{{ __('Horário') }}</strong></label>
                <input type="time" name="time" id="time" class="form-control">
            </div>
        </div>

        <div class="form-group mt-3">
            <label for="period" class="mb-2"><strong>{{ __('Repetir a cada') }}</strong></label>
            <select name="period" id="period" class="form-select" onchange="showDaysOfWeek('daysOfWeek', this)">
                <option value="">{{ __('Selecionar') }}</option>
                <option value="day">{{ __('Dia') }}</option>
                <option value="week">{{ __('Semana') }}</option>
                <option value="month">{{ __('Mês') }}</option>
                <option value="year">{{ __('Ano') }}</option>
            </select>
        </div>

        {{-- Mostrado só quando escolher "semana" --}}
        <div class="form-group mt-3" id="daysOfWeek" style="display: none;">
            <div class="alert alert-info p-2 mb-0" style="font-size:0.95em;">
                {{ __('A tarefa será repetida semanalmente no mesmo dia da semana da data de início.') }}
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
