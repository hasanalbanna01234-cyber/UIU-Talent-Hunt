import { useEffect, useState } from 'react';
import { Link, Navigate, Route, Routes, useNavigate } from 'react-router-dom';

const features = [['01', 'Showcase your talent', 'Share the work that deserves to be seen.'], ['02', 'Find your people', 'Connect with the creative UIU community.'], ['03', 'Earn recognition', 'Likes, comments, and a place on the leaderboard.']];

function Landing() {
  const [loginOpen, setLoginOpen] = useState(false);
  return <main className="landing"><header className="landing-nav"><Link className="brand" to="/">UIU <span>Talent Hunt</span></Link><button className="text-button" onClick={() => setLoginOpen(true)}>Sign in <span>↗</span></button></header><section className="hero"><div className="hero-copy"><p className="eyebrow">United International University / 2026</p><h1>Make your<br /><em>talent</em> impossible<br />to miss.</h1><p className="hero-intro">A living gallery for the voices, ideas, and performances shaping the UIU community.</p><button className="primary-button" onClick={() => setLoginOpen(true)}>Enter the hunt <span>→</span></button></div><div className="hero-art" aria-label="A collage of creative talent"><div className="art-label">DISCOVER<br />CREATE<br />RISE</div><div className="art-circle" /><div className="art-caption">Your next audience<br />is already here.</div></div></section><section className="feature-strip">{features.map(([number, title, copy]) => <article key={number}><span>{number}</span><div><h2>{title}</h2><p>{copy}</p></div></article>)}</section>{loginOpen && <LoginModal onClose={() => setLoginOpen(false)} />}</main>;
}

function LoginModal({ onClose }) {
  const navigate = useNavigate();
  const [studentId, setStudentId] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [pending, setPending] = useState(false);
  async function submit(event) {
    event.preventDefault();
    if (!studentId.trim() || !password) return setError('Please enter Student ID and Password.');
    setPending(true); setError('');
    try {
      const body = new URLSearchParams({ student_id: studentId.trim(), password, login_submit: '1' });
      const response = await fetch('index.php', { method: 'POST', body, credentials: 'include' });
      if (response.redirected && response.url.includes('dashboard.php')) navigate('/dashboard');
      else setError('Invalid Student ID or Password.');
    } catch { setError('The login service is unavailable. Start the PHP server and try again.'); }
    finally { setPending(false); }
  }
  return <div className="modal-backdrop" onMouseDown={onClose}><section className="login-modal" onMouseDown={event => event.stopPropagation()}><button className="close-button" onClick={onClose} aria-label="Close login">×</button><p className="eyebrow">Welcome back</p><h2>Join the hunt.</h2><p className="modal-copy">Log in to share your work and find the next standout talent.</p><form onSubmit={submit}><label>Student ID<input value={studentId} onChange={event => setStudentId(event.target.value)} placeholder="e.g. 011231001" autoFocus /></label><label>Password<input type="password" value={password} onChange={event => setPassword(event.target.value)} placeholder="Your password" /></label>{error && <p className="form-error">{error}</p>}<button className="primary-button full-width" disabled={pending}>{pending ? 'Signing in...' : 'Sign in'} <span>→</span></button></form><p className="register-prompt">New to Hunt? <Link to="/signup" onClick={onClose}>Create an account</Link></p></section></div>;
}

function Dashboard() {
  const [posts, setPosts] = useState([]);
  const [message, setMessage] = useState('Loading the community feed...');
  useEffect(() => { fetch('/api/dashboard.php', { credentials: 'include' }).then(response => response.json()).then(data => { if (data.error) setMessage(data.error); else { setPosts(data.posts || []); setMessage(''); } }).catch(() => setMessage('Start the PHP server to load the live feed.')); }, []);
  return <div className="app-page"><nav className="app-nav"><Link className="brand" to="/dashboard">UIU <span>Talent Hunt</span></Link><div className="app-links"><Link className="active" to="/dashboard">Discover</Link><Link to="/leaderboard">Leaderboard</Link><Link to="/competitions">Competitions</Link></div><Link className="profile-link" to="/profile">My profile</Link></nav><main className="dashboard-content"><p className="eyebrow">The community feed</p><h1>Discover the<br /><em>extraordinary.</em></h1><p className="hero-intro">Explore the latest creations from the UIU community, support your favorite talents, and share your own journey.</p><div className="dashboard-actions"><Link className="primary-button" to="/create">Create a post <span>→</span></Link><Link className="secondary-button" to="/leaderboard">See leaderboard</Link></div>{message && <p className="feed-message">{message}</p>}<div className="react-feed">{posts.map(post => <article className="post-preview" key={post.post_id}><p className="eyebrow">{post.talent_type}</p><h2>{post.title}</h2><p>{post.description || 'A new creation from the UIU community.'}</p><small>{post.like_count} likes · {post.comment_count} comments · {post.share_count} shares</small></article>)}</div></main></div>;
}

function Signup() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ full_name: '', student_id: '', email: '', department: '', password: '', confirm_password: '' });
  const [error, setError] = useState('');
  const [pending, setPending] = useState(false);
  function update(event) { setForm({ ...form, [event.target.name]: event.target.value }); }
  async function submit(event) { event.preventDefault(); setPending(true); setError(''); try { const response = await fetch('signup.php', { method: 'POST', body: new URLSearchParams(form), credentials: 'include' }); if (response.redirected && response.url.includes('dashboard.php')) navigate('/dashboard'); else setError('Unable to create the account. Check your details and try again.'); } catch { setError('The signup service is unavailable. Start the PHP server and try again.'); } finally { setPending(false); } }
  return <div className="signup-page"><Link className="brand" to="/">UIU <span>Talent Hunt</span></Link><section className="signup-panel"><p className="eyebrow">Start your journey</p><h1>Create your<br /><em>account.</em></h1><form className="signup-grid" onSubmit={submit}>{[['full_name', 'Full name'], ['student_id', 'Student ID'], ['email', 'UIU email']].map(([name, label]) => <label key={name}>{label}<input name={name} value={form[name]} onChange={update} required={name !== 'email'} /></label>)}<label>Department<select name="department" value={form.department} onChange={update} required><option value="">Select department</option>{['CSE', 'EEE', 'CE', 'ENGLISH', 'BBA', 'MEDIA', 'PHARMACY'].map(department => <option key={department}>{department}</option>)}</select></label><label>Password<input name="password" type="password" value={form.password} onChange={update} required /></label><label>Confirm password<input name="confirm_password" type="password" value={form.confirm_password} onChange={update} required /></label>{error && <p className="form-error">{error}</p>}<button className="primary-button" disabled={pending}>{pending ? 'Creating...' : 'Create account'} <span>→</span></button></form></section></div>;
}
function Placeholder({ title, description }) { return <div className="app-page"><nav className="app-nav"><Link className="brand" to="/dashboard">UIU <span>Talent Hunt</span></Link><Link className="profile-link" to="/dashboard">Back to discover</Link></nav><main className="dashboard-content compact"><p className="eyebrow">UIU Talent Hunt</p><h1>{title}</h1><p className="hero-intro">{description}</p></main></div>; }
export default function App() { return <Routes><Route path="/" element={<Landing />} /><Route path="/signup" element={<Signup />} /><Route path="/dashboard" element={<Dashboard />} /><Route path="/leaderboard" element={<Placeholder title="The leaderboard." description="See which talents are moving the UIU community right now." />} /><Route path="/competitions" element={<Placeholder title="Competitions." description="Upcoming challenges and campus events will live here." />} /><Route path="/profile" element={<Placeholder title="Your profile." description="Your posts, drafts, and recognition in one place." />} /><Route path="/create" element={<Placeholder title="Create something." description="Choose video, audio, photo, or blog to share your talent." />} /><Route path="*" element={<Navigate to="/" replace />} /></Routes>; }