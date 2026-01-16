import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter, Route, Routes } from 'react-router-dom'
import { Main } from '../pages'
import "./index.scss";

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <Routes>
        <Route path='/' element={<Main />}></Route>
      </Routes>
    </BrowserRouter>
  </StrictMode>,
)
