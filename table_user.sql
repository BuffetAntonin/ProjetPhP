-- 1. Table ROLE
CREATE TABLE IF NOT EXISTS public."role"
(
    id_role SERIAL NOT NULL,
    nom_role character varying(50) COLLATE pg_catalog."default" NOT NULL,
    CONSTRAINT role_pkey PRIMARY KEY (id_role),
    CONSTRAINT role_nom_role_key UNIQUE (nom_role)
)
TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."role"
    OWNER to devuser;


-- 2. Table UTILISATEUR
CREATE TABLE IF NOT EXISTS public."utilisateur"
(
    id_utilisateur BIGSERIAL NOT NULL,
    id_role integer NOT NULL,
    nom_utilisateur character varying(100) COLLATE pg_catalog."default" NOT NULL,
    email character varying(255) COLLATE pg_catalog."default" NOT NULL,
    mot_de_passe_hash character varying(255) COLLATE pg_catalog."default" NOT NULL,
    date_inscription timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    est_actif boolean DEFAULT false,
    token_confirmation character varying(60) COLLATE pg_catalog."default",
    token_reset_mdp character varying(60) COLLATE pg_catalog."default",
    date_expiration_token timestamp without time zone,
    CONSTRAINT utilisateur_pkey PRIMARY KEY (id_utilisateur),
    CONSTRAINT utilisateur_email_key UNIQUE (email),
    CONSTRAINT utilisateur_id_role_fkey FOREIGN KEY (id_role)
        REFERENCES public."role" (id_role) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)
TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."utilisateur"
    OWNER to devuser;


-- 3. Table PAGE
CREATE TABLE IF NOT EXISTS public."page"
(
    id_page BIGSERIAL NOT NULL,
    titre character varying(255) COLLATE pg_catalog."default" NOT NULL,
    slug character varying(255) COLLATE pg_catalog."default" NOT NULL,
    contenu text COLLATE pg_catalog."default" NOT NULL,
    date_creation timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    date_modification timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    est_publie boolean DEFAULT false,
    id_utilisateur bigint NOT NULL,
    CONSTRAINT page_pkey PRIMARY KEY (id_page),
    CONSTRAINT page_slug_key UNIQUE (slug),
    CONSTRAINT page_id_utilisateur_fkey FOREIGN KEY (id_utilisateur)
        REFERENCES public."utilisateur" (id_utilisateur) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)
TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."page"
    OWNER to devuser;


-- 4. Table CATEGORIE
CREATE TABLE IF NOT EXISTS public."categorie"
(
    id_categorie SERIAL NOT NULL,
    nom_categorie character varying(100) COLLATE pg_catalog."default" NOT NULL,
    slug character varying(100) COLLATE pg_catalog."default" NOT NULL,
    CONSTRAINT categorie_pkey PRIMARY KEY (id_categorie),
    CONSTRAINT categorie_nom_categorie_key UNIQUE (nom_categorie),
    CONSTRAINT categorie_slug_key UNIQUE (slug)
)
TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."categorie"
    OWNER to devuser;


-- 5. Table ARTICLE
CREATE TABLE IF NOT EXISTS public."article"
(
    id_article BIGSERIAL NOT NULL,
    titre character varying(255) COLLATE pg_catalog."default" NOT NULL,
    slug character varying(255) COLLATE pg_catalog."default" NOT NULL,
    extrait text COLLATE pg_catalog."default",
    contenu text COLLATE pg_catalog."default" NOT NULL,
    date_publication timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    date_modification timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    est_publie boolean DEFAULT false,
    id_utilisateur bigint NOT NULL,
    CONSTRAINT article_pkey PRIMARY KEY (id_article),
    CONSTRAINT article_slug_key UNIQUE (slug),
    CONSTRAINT article_id_utilisateur_fkey FOREIGN KEY (id_utilisateur)
        REFERENCES public."utilisateur" (id_utilisateur) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)
TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."article"
    OWNER to devuser;


-- 6. Table de liaison ARTICLE_CATEGORIE
CREATE TABLE IF NOT EXISTS public."article_categorie"
(
    id_categorie integer NOT NULL,
    id_article bigint NOT NULL,
    CONSTRAINT article_categorie_pkey PRIMARY KEY (id_categorie, id_article),
    CONSTRAINT article_categorie_id_categorie_fkey FOREIGN KEY (id_categorie)
        REFERENCES public."categorie" (id_categorie) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE,
    CONSTRAINT article_categorie_id_article_fkey FOREIGN KEY (id_article)
        REFERENCES public."article" (id_article) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE
)
TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."article_categorie"
    OWNER to devuser;