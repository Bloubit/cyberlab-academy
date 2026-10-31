import Head from 'next/head'
import '../styles/globals.css'
import { AuthProvider } from '../context/AuthContext'

function MyApp({ Component, pageProps }) {
  return (
    <AuthProvider>
      <Head>
        <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
        <link rel="alternate icon" href="/favicon.ico" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="theme-color" content="#0f0f23" />
        <meta name="description" content="CyberLab Academy - Master cybersecurity through hands-on labs" />
      </Head>
      <Component {...pageProps} />
    </AuthProvider>
  )
}

export default MyApp
