import React, { useEffect, useState, useRef } from "react";
import { CreateTopic } from "./components/CreateTopic";
import { TopicDetail } from "./components/TopicDetail";
import { WorksRanking } from "./components/WorksRanking";
import { WorkEditor } from "./components/WorkEditor";
import { ThemeToggle } from "./components/ThemeToggle";
import { Home } from "./components/Home";
import { UserProfile } from "./components/UserProfile";
import { WikiSources } from "./components/WikiSources";
import { NotificationBell } from "./components/NotificationBell";
import { SearchBar } from "./components/SearchBar";
import { TopicsList } from "./components/TopicsList";
import { RoleManagement } from "./components/RoleManagement";
import { topicService } from "./services/topicService";
import { authService } from "./services/authService";
import { Login } from "./components/Login";
import { Register } from "./components/Register";

function App() {
  const [topics, setTopics]                 = useState([]);
  const [loading, setLoading]               = useState(true);
  const [user, setUser]                     = useState(null);
  const [showRegister, setShowRegister]     = useState(false);
  const [selectedTopicId, setSelectedTopicId] = useState(null);
  const [showRanking, setShowRanking]       = useState(false);
  const [showWorkManager, setShowWorkManager] = useState(false);
  const [showHome, setShowHome]             = useState(true);
  const [showProfile, setShowProfile]       = useState(false);
  const [showWiki, setShowWiki]             = useState(false);
  const [showAdmin, setShowAdmin]            = useState(false);
  const createTopicRef = useRef(null);

  useEffect(() => { checkUser(); }, []);
  useEffect(() => {
    if (user) loadTopics();
    else { setTopics([]); setLoading(false); }
  }, [user]);

  const checkUser = async () => {
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
      setUser(userData && userData.id ? userData : null);
    } catch {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      setUser(null);
    }
    setLoading(false);
  };

  const loadTopics = async () => {
    try {
      const response = await topicService.getAll();
      setTopics(Array.isArray(response.data) ? response.data : []);
    } catch (error) {
      console.error("Erro ao carregar tópicos:", error);
      setTopics([]);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = async () => {
    try { await authService.logout(); } catch (err) { console.error("Erro ao sair:", err); }
    finally {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      setUser(null);
      setSelectedTopicId(null);
      setShowRanking(false);
      setShowWorkManager(false);
      setShowHome(true);
      setShowProfile(false);
      setShowWiki(false);
    }
  };

  const handleViewTopic       = (id)  => { 
    setSelectedTopicId(id); 
    setShowRanking(false); 
    setShowWorkManager(false); 
    setShowHome(false); 
    setShowProfile(false); 
    setShowWiki(false); 
  };
  
  const handleBackToTopics    = ()    => { 
    setSelectedTopicId(null); 
    setShowRanking(false); 
    setShowHome(true); 
    setShowProfile(false);
    setShowWiki(false); 
  };
  
  const handleShowRanking     = ()    => { 
    setShowRanking(true); 
    setSelectedTopicId(null); 
    setShowWorkManager(false); 
    setShowHome(false); 
    setShowProfile(false); 
    setShowWiki(false); 
  };
  
  const handleBackFromRanking = ()    => { 
    setShowRanking(false); 
    setShowHome(true); 
  };
  
  const handleShowWorkManager = ()    => { 
    setShowWorkManager(true); 
    setSelectedTopicId(null); 
    setShowRanking(false); 
    setShowHome(false); 
    setShowProfile(false); 
    setShowWiki(false); 
  };
  
  const handleBackFromWorkManager = () => { 
    setShowWorkManager(false); 
    setShowHome(true); 
  };
  
  const handleWorkSaved       = ()    => { 
    setShowWorkManager(false); 
    setShowHome(true); 
  };
  
  const handleTopicDeleted    = ()    => { 
    setSelectedTopicId(null); 
    setShowHome(true); 
    loadTopics(); 
  };
  
  const handleShowHome        = ()    => { 
    setShowHome(true); 
    setSelectedTopicId(null); 
    setShowRanking(false); 
    setShowWorkManager(false); 
    setShowProfile(false); 
    setShowWiki(false); 
  };
  
  const handleShowProfile     = ()    => { 
    setShowProfile(true); 
    setSelectedTopicId(null); 
    setShowRanking(false); 
    setShowWorkManager(false); 
    setShowHome(false); 
    setShowWiki(false); 
  };
  
  const handleBackFromProfile = ()    => { 
    setShowProfile(false); 
    setShowHome(true); 
  };
  
  const handleShowWiki        = ()    => { 
    setShowWiki(true); 
    setSelectedTopicId(null); 
    setShowRanking(false); 
    setShowWorkManager(false); 
    setShowHome(false); 
    setShowProfile(false); 
  };
  
  const handleBackFromWiki    = ()    => { 
    setShowWiki(false); 
    setShowHome(true); 
  };
  
  const handleShowAdmin       = ()    => { 
    setShowAdmin(true); 
    setSelectedTopicId(null); 
    setShowRanking(false); 
    setShowWorkManager(false); 
    setShowHome(false); 
    setShowProfile(false); 
    setShowWiki(false); 
  };
  
  const handleBackFromAdmin   = ()    => { 
    setShowAdmin(false); 
    setShowHome(true); 
  };
  
  const handleNewTopic        = ()    => { createTopicRef.current?.focus(); window.scrollTo({ top: 0, behavior: 'smooth' }); };

  /* ── Loading inicial ── */
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

  /* ── Telas de autenticação ── */
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
                Já tem conta?{" "}
                <button
                  style={{ color: 'var(--color-primary)', background: 'none', border: 'none', cursor: 'pointer', fontWeight: 'var(--font-medium)', padding: 0 }}
                  onClick={() => setShowRegister(false)}
                >
                  Entrar
                </button>
              </p>
            </>
          ) : (
            <>
              <Login onLogin={checkUser} />
              <p style={{ marginTop: 'var(--space-4)', color: 'var(--color-text-secondary)', fontSize: 'var(--text-sm)' }}>
                Não tem conta?{" "}
                <button
                  style={{ color: 'var(--color-primary)', background: 'none', border: 'none', cursor: 'pointer', fontWeight: 'var(--font-medium)', padding: 0 }}
                  onClick={() => setShowRegister(true)}
                >
                  Registrar
                </button>
              </p>
            </>
          )}
        </div>
      </div>
    );
  }

  /* ── App autenticado ── */
  return (
    <div style={{ minHeight: '100svh', display: 'flex', flexDirection: 'column', background: 'var(--color-bg)' }}>

      {/* Navbar principal */}
      <nav className="navbar">
        <div className="navbar__inner">
          <button
            className="navbar__brand navbar__brand--button"
            onClick={handleShowHome}
          >
            📚 Amigos Leituras
          </button>

          <div className="navbar__actions">
            <button
              onClick={handleShowHome}
              className={`btn btn--sm ${showHome && !selectedTopicId && !showRanking && !showWorkManager && !showProfile ? 'btn--primary' : 'btn--ghost'}`}
            >
              🏠 Início
            </button>
            <button
              onClick={handleShowWorkManager}
              className={`btn btn--sm ${showWorkManager ? 'btn--primary' : 'btn--ghost'}`}
            >
              📖 Obras
            </button>
            <button
              onClick={handleShowRanking}
              className={`btn btn--sm ${showRanking ? 'btn--primary' : 'btn--ghost'}`}
            >
              🏆 Ranking
            </button>
            <button
              onClick={handleShowWiki}
              className={`btn btn--sm ${showWiki ? 'btn--primary' : 'btn--ghost'}`}
            >
              🔗 Wiki
            </button>
            {user?.role?.name === 'owner' && (
              <button
                onClick={handleShowAdmin}
                className={`btn btn--sm ${showAdmin ? 'btn--primary' : 'btn--ghost'}`}
              >
                ⚙️ Admin
              </button>
            )}

            <div style={{ width: '1px', background: 'var(--color-border)', height: '20px', margin: '0 var(--space-1)' }} />

            <ThemeToggle variant="icon" />

            <SearchBar
              onSelect={(item) => {
                if (item.type === 'topic' || item.type === 'topic') {
                  handleViewTopic(item.id);
                }
              }}
            />

            <NotificationBell user={user} />

            <button
              onClick={handleShowProfile}
              className="btn btn--sm btn--ghost navbar__profile-btn"
            >
              👤 {user.name} {user.role && <span style={{ opacity: 0.7, fontSize: '0.85em' }}>({user.role.display_name})</span>}
            </button>

            <button onClick={handleLogout} className="btn btn--sm btn--ghost">
              Sair
            </button>
          </div>
        </div>
      </nav>

      {/* Conteúdo principal */}
      <main style={{ flex: 1, maxWidth: 'var(--container-xl)', width: '100%', margin: '0 auto', padding: 'var(--space-8) var(--space-6)' }}>

        {selectedTopicId ? (
          <TopicDetail
            topicId={selectedTopicId}
            user={user}
            onBack={handleBackToTopics}
            onTopicDeleted={handleTopicDeleted}
          />

        ) : showRanking ? (
          <WorksRanking onBack={handleBackFromRanking} />

        ) : showWorkManager ? (
          <WorkEditor
            onWorkSaved={handleWorkSaved}
            onCancel={handleBackFromWorkManager}
          />

        ) : showProfile ? (
          <UserProfile
            user={user}
            onViewTopic={handleViewTopic}
            onBack={handleBackFromProfile}
          />

        ) : showWiki ? (
          <WikiSources />

        ) : showAdmin ? (
          <RoleManagement 
            onBack={handleBackFromAdmin} 
            user={user}
          />

        ) : showHome ? (
          <>
            <Home
              user={user}
              onViewTopic={handleViewTopic}
              onShowRanking={handleShowRanking}
              onShowWorks={handleShowWorkManager}
              onNewTopic={handleNewTopic}
            />

            <div style={{ marginTop: 'var(--space-8)' }}>
              <CreateTopic ref={createTopicRef} onTopicCreated={loadTopics} />

              <hr className="divider" />

              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 'var(--space-4)' }}>
                <h2 style={{ margin: 0 }}>Todas as Experiências</h2>
                <span className="badge badge--neutral">{topics.length} tópico{topics.length !== 1 ? 's' : ''}</span>
              </div>

              <TopicsList
                topics={topics}
                user={user}
                onViewTopic={handleViewTopic}
              />
            </div>
          </>
        ) : null}
      </main>
    </div>
  );
}

export default App;
