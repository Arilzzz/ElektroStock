import { ref } from 'vue'

const token = ref(localStorage.getItem('token'))
const user = ref(
    JSON.parse(localStorage.getItem('user') || 'null')
)

export function useAuth() {
    const isAuthenticated = () => {
        return !!token.value
}

    const setAuth = (newToken, newUser) => {
        token.value = newToken
        user.value = newUser

        localStorage.setItem('token', newToken)
        localStorage.setItem('user', JSON.stringify(newUser))
    }

    const logout = () => {
        token.value = null
        user.value = null

        localStorage.removeItem('token')
        localStorage.removeItem('user')
    }

    return {
        token,
        user,
        isAuthenticated,
        setAuth,
        logout,
    }
}