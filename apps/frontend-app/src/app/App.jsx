import { BrowserRouter, Routes, Route } from "react-router"
import LoginPage from "../pages/auth/LoginPage"

function App() {
  return (
    <>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
        </Routes>
      </BrowserRouter>,
    </>
  )
}

export default App
