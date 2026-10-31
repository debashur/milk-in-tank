// Запросы к API. Адрес /api проксирует Vite на бэкенд, см. vite.config.js.

async function request(path, options = {}) {
  let response
  try {
    response = await fetch(`/api${path}`, {
      ...options,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...options.headers,
      },
    })
  } catch {
    throw new Error('Сервер не отвечает. Запусти бэкенд: в папке backend команда ./yii serve')
  }

  const body = await response.json().catch(() => null)
  if (!response.ok) {
    throw new Error(errorMessage(body))
  }

  return body?.data
}

function errorMessage(body) {
  if (body?.error_message && body.error_message !== 'Validation failed.') {
    return body.error_message
  }

  const details = body?.error_data
  if (details && typeof details === 'object') {
    const lines = Object.values(details).flat().filter((line) => typeof line === 'string')
    if (lines.length > 0) {
      return lines.join(' ')
    }
  }

  return 'Не удалось выполнить запрос.'
}

export function fetchTanks() {
  return request('/tanks')
}

export function fetchPours() {
  return request('/pours')
}

export function createPour(personName, liters) {
  return request('/pours', {
    method: 'POST',
    body: JSON.stringify({ personName, liters }),
  })
}
