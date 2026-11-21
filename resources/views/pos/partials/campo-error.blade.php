@error($campo, $bolsa ?? 'default')
    <p id="error-{{ str_replace(['.', '[', ']'], '-', $campo) }}" class="error-campo-pos">{{ $message }}</p>
@enderror
