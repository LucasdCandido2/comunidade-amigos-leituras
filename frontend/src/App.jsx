import React, { useState, useEffect, useMemo, useCallback, Suspense, lazy } from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ThemeToggle } from './components/ThemeToggle';
import { NotificationBell } from './components/NotificationBell';
import { SearchBar } from './components/SearchBar';
import { TopicsList } from './components/TopicsList';
import { topicService } from './services/topicService';
import { authService } from './services/authService';
import { Login } from './components/Login';
import { Register } from './components/Register';
import { Home } from './components/Home';
import { TopicDetail } from './components/TopicDetail';
import { WorksRanking } from './components/WorksRanking';
import { WorksByLetter } from './components/WorksByLetter';
import { WorkEditor } from './components/WorkEditor';
import { UserProfile } from './components/UserProfile';
import { WikiSources } from './components/WikiSources';
import { CreateTopic } from './components/CreateTopic';
import { RoleManagement } from './components/RoleManagement';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,
      gcTime: 10 * 60 * 1000,
      refetchOnWindowFocus: false,
      retry: 1,
    },
  },
});

const ViewState = {
  HOME: 'home',
  TOPIC: 'topic',
  RANKING: 'ranking',
  WORKS: 'works',
  LETTERS: 'letters',
  PROFILE: 'profile',
  WIKI: 'wiki',
  ADMIN: 'admin',
};

const LoadingFallback = () => (
  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 'var(--space-8)' }}>
    <div className="spinner" />
    <span style={{ marginLeft: 'var(--space-3)' }}>Carregando...</span>
  </div>
);

function useAppViewModel() {
  const [currentView, setCurrentView] = useState(ViewState.HOME);
  const [selectedTopicId, setSelectedTopicId] = useState(null);
  const [topics, setTopics] = useState([]);
  const [loading, setLoading] = useState(true);
  const [user, setUser] = useState(null);
  const [showRegister, setShowRegister] = useState(false);

  const checkUser = useCallback(async () => {
    const token = localStorage.getItem('token');
    if (!token) {
      setUser(null);
      setLoading(false);
      return;
    }

    const cachedUser = localStorage.getItem('user');
    if (cachedUser) {
      try {
        const parsed = JSON.parse(cachedUser);
        if (parsed && parsed.id) {
          setUser(parsed);
          setLoading(false);
          return;
        }
      } catch (e) {
        localStorage.removeItem('user');
      }
    }

    try {
      const userData = await authService.getUser();
      if (userData && userData.id) {
        setUser(userData);
        localStorage.setItem('user', JSON.stringify(userData));
      } else {
        setUser(null);
      }
    } catch {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      setUser(null);
    }
    setLoading(false);
  }, []);

  const loadTopics = useCallback(async () => {
    try {
      const response = await topicService.getAll();
      const topicsData = Array.isArray(response.data) ? response.data : (response.data?.data || []);
      setTopics(topicsData);
    } catch (error) {
      console.error('Erro ao carregar tópicos:', error);
      setTopics([]);
    }
  }, []);

  useEffect(() => { checkUser(); }, [checkUser]);

  useEffect(() => {
    if (user) {
      loadTopics();
    } else {
      setTopics([]);
      setLoading(false);
    }
  }, [user, loadTopics]);

  const navigate = useCallback((view, params = {}) => {
    setCurrentView(view);
    if (params.topicId !== undefined) setSelectedTopicId(params.topicId);
    if (view !== ViewState.TOPIC) setSelectedTopicId(null);
  }, []);

  const handleLogout = useCallback(async () => {
    try { await authService.logout(); } catch (err) { console.error('Erro ao sair:', err); }
    finally {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      setUser(null);
      setSelectedTopicId(null);
      setCurrentView(ViewState.HOME);
    }
  }, []);

  const handleTopicCreated = useCallback(() => {
    loadTopics();
  }, [loadTopics]);

  const handleTopicDeleted = useCallback(() => {
    setSelectedTopicId(null);
    setCurrentView(ViewState.HOME);
    loadTopics();
  }, [loadTopics]);

  const handleWorkSaved = useCallback(() => {
    setCurrentView(ViewState.HOME);
  }, []);

  const isAdminVisible = useMemo(() => {
    if (!user) return false;
    const userRoles = user.roles?.map(r => r.name) || [];
    return userRoles.includes('owner') || userRoles.includes('admin') || userRoles.includes('moderator');
  }, [user]);
  const isHomeActive = useMemo(() => currentView === ViewState.HOME && !selectedTopicId, [currentView, selectedTopicId]);

  return {
    state: { currentView, selectedTopicId, topics, loading, user, showRegister },
    actions: { navigate, handleLogout, handleTopicCreated, handleTopicDeleted, handleWorkSaved, setShowRegister, checkUser },
    computed: { isAdminVisible, isHomeActive },
  };
}

function App() {
  const { state, actions, computed } = useAppViewModel();
  const { currentView, selectedTopicId, topics, loading, user, showRegister } = state;
  const { navigate, handleLogout, handleTopicCreated, handleTopicDeleted, handleWorkSaved, setShowRegister, checkUser } = actions;
  const { isAdminVisible, isHomeActive } = computed;

  const createTopicRef = React.useRef(null);

  if (loading) {
    return (
      <div style={{ minHeight: '100svh', display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'var(--color-bg)' }}>
        <div className="loading-state">
          <div className="spinner" />
          <span>Carregando...</span>
        </div>
      </div>
    );
  }

  if (!user) {
    return (
      <div style={{ minHeight: '100svh', display: 'flex', flexDirection: 'column', background: 'var(--color-bg)' }}>
        <nav className="navbar">
          <div className="navbar__inner">
            <span className="navbar__brand">📚 Comunidade Amigos Leituras</span>
            <ThemeToggle variant="label" />
          </div>
        </nav>
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: 'var(--space-8)' }}>
          {showRegister ? (
            <>
              <Register onRegister={() => { setShowRegister(false); checkUser(); }} />
              <p style={{ marginTop: 'var(--space-4)', color: 'var(--color-text-secondary)', fontSize: 'var(--text-sm)' }}>
                Já tem conta?{' '}
                <button style={{ color: 'var(--color-primary)', background: 'none', border: 'none', cursor: 'pointer', fontWeight: 'var(--font-medium)', padding: 0 }} onClick={() => setShowRegister(false)}>Entrar</button>
              </p>
            </>
          ) : (
            <>
              <Login onLogin={checkUser} />
              <p style={{ marginTop: 'var(--space-4)', color: 'var(--color-text-secondary)', fontSize: 'var(--text-sm)' }}>
                Não tem conta?{' '}
                <button style={{ color: 'var(--color-primary)', background: 'none', border: 'none', cursor: 'pointer', fontWeight: 'var(--font-medium)', padding: 0 }} onClick={() => setShowRegister(true)}>Registrar</button>
              </p>
            </>
          )}
        </div>
      </div>
    );
  }

  const renderContent = () => {
    if (selectedTopicId) {
      return (
        <TopicDetail topicId={selectedTopicId} user={user} onBack={() => navigate(ViewState.HOME)} onTopicDeleted={handleTopicDeleted} />
      );
    }

    switch (currentView) {
      case ViewState.RANKING:
        return <WorksRanking onBack={() => navigate(ViewState.HOME)} />;
      case ViewState.LETTERS:
        return <WorksByLetter onBack={() => navigate(ViewState.HOME)} />;
      case ViewState.WORKS:
        return <WorkEditor onWorkSaved={handleWorkSaved} onCancel={() => navigate(ViewState.HOME)} />;
      case ViewState.PROFILE:
        return <UserProfile user={user} onViewTopic={(id) => navigate(ViewState.TOPIC, { topicId: id })} onBack={() => navigate(ViewState.HOME)} />;
      case ViewState.WIKI:
        return <WikiSources />;
      case ViewState.ADMIN:
        return <RoleManagement onBack={() => navigate(ViewState.HOME)} user={user} />;
      default:
        return (
          <>
            <Home user={user} onViewTopic={(id) => navigate(ViewState.TOPIC, { topicId: id })} onShowRanking={() => navigate(ViewState.RANKING)} onShowWorks={() => navigate(ViewState.WORKS)} onShowLetters={() => navigate(ViewState.LETTERS)} onNewTopic={() => createTopicRef.current?.focus()} />
            <div style={{ marginTop: 'var(--space-8)' }}>
              <CreateTopic ref={createTopicRef} onTopicCreated={handleTopicCreated} />
              <hr className="divider" />
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 'var(--space-4)' }}>
                <h2 style={{ margin: 0 }}>Todas as Experiências</h2>
                <span className="badge badge--neutral">{topics.length} tópico{topics.length !== 1 ? 's' : ''}</span>
              </div>
              <TopicsList topics={topics} user={user} onViewTopic={(id) => navigate(ViewState.TOPIC, { topicId: id })} />
            </div>
          </>
        );
    }
  };

  return (
    <QueryClientProvider client={queryClient}>
      <div style={{ minHeight: '100svh', display: 'flex', flexDirection: 'column', background: 'var(--color-bg)' }}>
        <nav className="navbar">
          <div className="navbar__inner">
            <button className="navbar__brand navbar__brand--button" onClick={() => navigate(ViewState.HOME)}>📚 Amigos Leituras</button>
            <div className="navbar__actions">
              <button onClick={() => navigate(ViewState.HOME)} className={`btn btn--sm ${isHomeActive ? 'btn--primary' : 'btn--ghost'}`}>🏠 Início</button>
              <button onClick={() => navigate(ViewState.WORKS)} className={`btn btn--sm ${currentView === ViewState.WORKS ? 'btn--primary' : 'btn--ghost'}`}>📖 Obras</button>
              <button onClick={() => navigate(ViewState.RANKING)} className={`btn btn--sm ${currentView === ViewState.RANKING ? 'btn--primary' : 'btn--ghost'}`}>🏆 Ranking</button>
              <button onClick={() => navigate(ViewState.WIKI)} className={`btn btn--sm ${currentView === ViewState.WIKI ? 'btn--primary' : 'btn--ghost'}`}>🔗 Wiki</button>
              {isAdminVisible && <button onClick={() => navigate(ViewState.ADMIN)} className={`btn btn--sm ${currentView === ViewState.ADMIN ? 'btn--primary' : 'btn--ghost'}`}>⚙️ Admin</button>}
              <div style={{ width: '1px', background: 'var(--color-border)', height: '20px', margin: '0 var(--space-1)' }} />
              <ThemeToggle variant="icon" />
              <SearchBar onSelect={(item) => { if (item.type === 'topic') navigate(ViewState.TOPIC, { topicId: item.id }); }} />
              <NotificationBell user={user} />
              <button onClick={() => navigate(ViewState.PROFILE)} className="btn btn--sm btn--ghost navbar__profile-btn">
                👤 {user.name} {user.roles?.[0] && <span style={{ opacity: 0.7, fontSize: '0.85em' }}>({user.roles[0].display_name})</span>}
              </button>
              <button onClick={handleLogout} className="btn btn--sm btn--ghost">Sair</button>
            </div>
          </div>
        </nav>
        <main style={{ flex: 1, maxWidth: 'var(--container-xl)', width: '100%', margin: '0 auto', padding: 'var(--space-8) var(--space-6)' }}>
          {renderContent()}
        </main>
      </div>
    </QueryClientProvider>
  );
}

export default App;