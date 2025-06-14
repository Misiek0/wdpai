--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4 (Debian 17.4-1.pgdg120+2)
-- Dumped by pg_dump version 17.4

-- Started on 2025-06-14 18:12:59 UTC

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 224 (class 1259 OID 16734)
-- Name: driver_vehicle_assignments; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.driver_vehicle_assignments (
    id integer NOT NULL,
    driver_id integer,
    vehicle_id integer,
    assignment_date timestamp with time zone DEFAULT timezone('Europe/Warsaw'::text, now()) NOT NULL
);


ALTER TABLE public.driver_vehicle_assignments OWNER TO docker;

--
-- TOC entry 223 (class 1259 OID 16733)
-- Name: driver_vehicle_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.driver_vehicle_assignments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.driver_vehicle_assignments_id_seq OWNER TO docker;

--
-- TOC entry 3451 (class 0 OID 0)
-- Dependencies: 223
-- Name: driver_vehicle_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.driver_vehicle_assignments_id_seq OWNED BY public.driver_vehicle_assignments.id;


--
-- TOC entry 220 (class 1259 OID 16594)
-- Name: drivers; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.drivers (
    id integer NOT NULL,
    user_id integer,
    name character varying(100) NOT NULL,
    surname character varying(100) NOT NULL,
    phone character varying(12),
    email character varying(255),
    license_expiry date NOT NULL,
    medical_exam_expiry date NOT NULL,
    driver_status public.driver_status DEFAULT 'available'::public.driver_status NOT NULL,
    photo character varying(255)
);


ALTER TABLE public.drivers OWNER TO docker;

--
-- TOC entry 219 (class 1259 OID 16593)
-- Name: drivers_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.drivers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.drivers_id_seq OWNER TO docker;

--
-- TOC entry 3452 (class 0 OID 0)
-- Dependencies: 219
-- Name: drivers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.drivers_id_seq OWNED BY public.drivers.id;


--
-- TOC entry 222 (class 1259 OID 16607)
-- Name: vehicles; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.vehicles (
    id integer NOT NULL,
    user_id integer,
    brand character varying(50) NOT NULL,
    model character varying(50) NOT NULL,
    reg_number character varying(20) NOT NULL,
    mileage integer,
    vehicle_inspection_expiry date,
    oc_ac_expiry date,
    vin character varying(17),
    avg_fuel_consumption double precision,
    status public.vehicle_status DEFAULT 'available'::public.vehicle_status NOT NULL,
    current_latitude numeric(10,8),
    current_longitude numeric(11,8),
    last_location_update timestamp with time zone DEFAULT timezone('Europe/Warsaw'::text, now()),
    photo character varying(255),
    CONSTRAINT vehicles_avg_fuel_consumption_check CHECK ((avg_fuel_consumption > (0)::double precision)),
    CONSTRAINT vehicles_current_latitude_check CHECK (((current_latitude >= ('-90'::integer)::numeric) AND (current_latitude <= (90)::numeric))),
    CONSTRAINT vehicles_current_longitude_check CHECK (((current_longitude >= ('-180'::integer)::numeric) AND (current_longitude <= (180)::numeric))),
    CONSTRAINT vehicles_mileage_check CHECK ((mileage >= 0)),
    CONSTRAINT vehicles_vin_check CHECK ((length((vin)::text) = 17))
);


ALTER TABLE public.vehicles OWNER TO docker;

--
-- TOC entry 230 (class 1259 OID 16790)
-- Name: drivers_with_vehicles; Type: VIEW; Schema: public; Owner: docker
--

CREATE VIEW public.drivers_with_vehicles AS
 SELECT d.id,
    d.user_id,
    d.name,
    d.surname,
    d.phone,
    d.email,
    d.license_expiry,
    d.medical_exam_expiry,
    d.driver_status,
    d.photo,
    v.id AS vehicle_id,
    v.brand AS vehicle_brand,
    v.model AS vehicle_model,
    v.reg_number AS vehicle_reg_number,
    v.status AS vehicle_status
   FROM ((public.drivers d
     LEFT JOIN ( SELECT DISTINCT ON (driver_vehicle_assignments.driver_id) driver_vehicle_assignments.driver_id,
            driver_vehicle_assignments.vehicle_id,
            driver_vehicle_assignments.assignment_date
           FROM public.driver_vehicle_assignments
          ORDER BY driver_vehicle_assignments.driver_id, driver_vehicle_assignments.assignment_date DESC) a ON ((a.driver_id = d.id)))
     LEFT JOIN public.vehicles v ON ((v.id = a.vehicle_id)));


ALTER VIEW public.drivers_with_vehicles OWNER TO docker;

--
-- TOC entry 231 (class 1259 OID 16795)
-- Name: expiring_documents; Type: VIEW; Schema: public; Owner: docker
--

CREATE VIEW public.expiring_documents AS
 SELECT 'driver_license'::text AS document_type,
    d.id AS owner_id,
    (((d.name)::text || ' '::text) || (d.surname)::text) AS owner_name,
    d.license_expiry AS expiry_date,
    (d.license_expiry - CURRENT_DATE) AS days_remaining
   FROM public.drivers d
  WHERE ((d.license_expiry >= CURRENT_DATE) AND (d.license_expiry <= (CURRENT_DATE + '30 days'::interval)))
UNION ALL
 SELECT 'driver_medical'::text AS document_type,
    d.id AS owner_id,
    (((d.name)::text || ' '::text) || (d.surname)::text) AS owner_name,
    d.medical_exam_expiry AS expiry_date,
    (d.medical_exam_expiry - CURRENT_DATE) AS days_remaining
   FROM public.drivers d
  WHERE ((d.medical_exam_expiry >= CURRENT_DATE) AND (d.medical_exam_expiry <= (CURRENT_DATE + '30 days'::interval)))
UNION ALL
 SELECT 'vehicle_inspection'::text AS document_type,
    v.id AS owner_id,
    ((((((v.brand)::text || ' '::text) || (v.model)::text) || ' ('::text) || (v.reg_number)::text) || ')'::text) AS owner_name,
    v.vehicle_inspection_expiry AS expiry_date,
    (v.vehicle_inspection_expiry - CURRENT_DATE) AS days_remaining
   FROM public.vehicles v
  WHERE ((v.vehicle_inspection_expiry >= CURRENT_DATE) AND (v.vehicle_inspection_expiry <= (CURRENT_DATE + '30 days'::interval)))
UNION ALL
 SELECT 'vehicle_insurance'::text AS document_type,
    v.id AS owner_id,
    ((((((v.brand)::text || ' '::text) || (v.model)::text) || ' ('::text) || (v.reg_number)::text) || ')'::text) AS owner_name,
    v.oc_ac_expiry AS expiry_date,
    (v.oc_ac_expiry - CURRENT_DATE) AS days_remaining
   FROM public.vehicles v
  WHERE ((v.oc_ac_expiry >= CURRENT_DATE) AND (v.oc_ac_expiry <= (CURRENT_DATE + '30 days'::interval)));


ALTER VIEW public.expiring_documents OWNER TO docker;

--
-- TOC entry 226 (class 1259 OID 16752)
-- Name: notifications; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.notifications (
    id integer NOT NULL,
    user_id integer NOT NULL,
    message text NOT NULL,
    is_read boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT timezone('Europe/Warsaw'::text, now()) NOT NULL
);


ALTER TABLE public.notifications OWNER TO docker;

--
-- TOC entry 225 (class 1259 OID 16751)
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.notifications_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.notifications_id_seq OWNER TO docker;

--
-- TOC entry 3453 (class 0 OID 0)
-- Dependencies: 225
-- Name: notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.notifications_id_seq OWNED BY public.notifications.id;


--
-- TOC entry 218 (class 1259 OID 16583)
-- Name: users; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.users (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    surname character varying(100) NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL
);


ALTER TABLE public.users OWNER TO docker;

--
-- TOC entry 217 (class 1259 OID 16582)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO docker;

--
-- TOC entry 3454 (class 0 OID 0)
-- Dependencies: 217
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 228 (class 1259 OID 16769)
-- Name: vehicle_location_history; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.vehicle_location_history (
    id integer NOT NULL,
    vehicle_id integer,
    latitude numeric(10,8) NOT NULL,
    longitude numeric(11,8) NOT NULL,
    recorded_at timestamp with time zone DEFAULT timezone('Europe/Warsaw'::text, now()) NOT NULL,
    CONSTRAINT vehicle_location_history_latitude_check CHECK (((latitude >= ('-90'::integer)::numeric) AND (latitude <= (90)::numeric))),
    CONSTRAINT vehicle_location_history_longitude_check CHECK (((longitude >= ('-180'::integer)::numeric) AND (longitude <= (180)::numeric)))
);


ALTER TABLE public.vehicle_location_history OWNER TO docker;

--
-- TOC entry 227 (class 1259 OID 16768)
-- Name: vehicle_location_history_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.vehicle_location_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_location_history_id_seq OWNER TO docker;

--
-- TOC entry 3455 (class 0 OID 0)
-- Dependencies: 227
-- Name: vehicle_location_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.vehicle_location_history_id_seq OWNED BY public.vehicle_location_history.id;


--
-- TOC entry 221 (class 1259 OID 16606)
-- Name: vehicles_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.vehicles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicles_id_seq OWNER TO docker;

--
-- TOC entry 3456 (class 0 OID 0)
-- Dependencies: 221
-- Name: vehicles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.vehicles_id_seq OWNED BY public.vehicles.id;


--
-- TOC entry 229 (class 1259 OID 16785)
-- Name: vehicles_with_drivers; Type: VIEW; Schema: public; Owner: docker
--

CREATE VIEW public.vehicles_with_drivers AS
 SELECT v.id,
    v.user_id,
    v.brand,
    v.model,
    v.reg_number,
    v.mileage,
    v.vehicle_inspection_expiry,
    v.oc_ac_expiry,
    v.vin,
    v.avg_fuel_consumption,
    v.status,
    v.current_latitude,
    v.current_longitude,
    v.last_location_update,
    v.photo,
    d.id AS driver_id,
    d.name AS driver_name,
    d.surname AS driver_surname,
    d.phone AS driver_phone,
    d.driver_status
   FROM ((public.vehicles v
     LEFT JOIN ( SELECT DISTINCT ON (driver_vehicle_assignments.vehicle_id) driver_vehicle_assignments.vehicle_id,
            driver_vehicle_assignments.driver_id,
            driver_vehicle_assignments.assignment_date
           FROM public.driver_vehicle_assignments
          ORDER BY driver_vehicle_assignments.vehicle_id, driver_vehicle_assignments.assignment_date DESC) a ON ((a.vehicle_id = v.id)))
     LEFT JOIN public.drivers d ON ((d.id = a.driver_id)));


ALTER VIEW public.vehicles_with_drivers OWNER TO docker;

--
-- TOC entry 3248 (class 2604 OID 16737)
-- Name: driver_vehicle_assignments id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.driver_vehicle_assignments ALTER COLUMN id SET DEFAULT nextval('public.driver_vehicle_assignments_id_seq'::regclass);


--
-- TOC entry 3243 (class 2604 OID 16597)
-- Name: drivers id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.drivers ALTER COLUMN id SET DEFAULT nextval('public.drivers_id_seq'::regclass);


--
-- TOC entry 3250 (class 2604 OID 16755)
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.notifications ALTER COLUMN id SET DEFAULT nextval('public.notifications_id_seq'::regclass);


--
-- TOC entry 3242 (class 2604 OID 16586)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 3253 (class 2604 OID 16772)
-- Name: vehicle_location_history id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.vehicle_location_history ALTER COLUMN id SET DEFAULT nextval('public.vehicle_location_history_id_seq'::regclass);


--
-- TOC entry 3245 (class 2604 OID 16610)
-- Name: vehicles id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.vehicles ALTER COLUMN id SET DEFAULT nextval('public.vehicles_id_seq'::regclass);


--
-- TOC entry 3441 (class 0 OID 16734)
-- Dependencies: 224
-- Data for Name: driver_vehicle_assignments; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.driver_vehicle_assignments (id, driver_id, vehicle_id, assignment_date) VALUES (40, 38, 115, '2025-06-05 20:54:59.218214+00');
INSERT INTO public.driver_vehicle_assignments (id, driver_id, vehicle_id, assignment_date) VALUES (42, 39, 119, '2025-06-05 20:59:30.90928+00');
INSERT INTO public.driver_vehicle_assignments (id, driver_id, vehicle_id, assignment_date) VALUES (44, 43, 120, '2025-06-05 21:03:37.850002+00');


--
-- TOC entry 3437 (class 0 OID 16594)
-- Dependencies: 220
-- Data for Name: drivers; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.drivers (id, user_id, name, surname, phone, email, license_expiry, medical_exam_expiry, driver_status, photo) VALUES (38, 14, 'Michał', 'Wszołek', '508678037', 'michal6426@gmail.com', '2033-08-13', '2027-05-28', 'on_road', 'man.png');
INSERT INTO public.drivers (id, user_id, name, surname, phone, email, license_expiry, medical_exam_expiry, driver_status, photo) VALUES (40, 14, 'Piotr', 'Kowalski', '666432232', 'piter@gmail.eu', '2026-10-17', '2026-07-24', 'on_leave', 'man.png');
INSERT INTO public.drivers (id, user_id, name, surname, phone, email, license_expiry, medical_exam_expiry, driver_status, photo) VALUES (39, 14, 'Karolina', 'Statek', '123123123', 'karolina@elo.pl', '2025-12-20', '2026-06-04', 'on_road', 'woman.png');
INSERT INTO public.drivers (id, user_id, name, surname, phone, email, license_expiry, medical_exam_expiry, driver_status, photo) VALUES (41, 14, 'Marceli', 'Kowalczyk', '333253343', 'marceli23@gmail.com', '2026-04-12', '2026-02-18', 'on_leave', 'man.png');
INSERT INTO public.drivers (id, user_id, name, surname, phone, email, license_expiry, medical_exam_expiry, driver_status, photo) VALUES (43, 14, 'Adam', 'Sandler', '223111554', 'adam@example.com', '2024-06-12', '2025-12-24', 'on_road', 'man.png');


--
-- TOC entry 3443 (class 0 OID 16752)
-- Dependencies: 226
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (138, 14, 'Vehicle id #121 successfully added', true, '2025-06-14 10:38:49.364667+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (148, 14, 'Vehicle #116 OC/AC expires on 2025-06-27', false, '2025-06-14 17:31:43.762657+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (150, 14, 'Vehicle #119 OC/AC expires on 2025-06-29', false, '2025-06-14 17:31:43.890755+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (151, 14, 'Vehicle id #122 successfully added', false, '2025-06-14 17:34:35.070724+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (149, 14, 'Vehicle #114 OC/AC expires on 2025-06-28', true, '2025-06-14 17:31:43.82266+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (71, 14, 'Vehicle id #114 successfully added', true, '2025-06-05 20:49:43.193993+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (72, 14, 'Vehicle id #115 successfully added', true, '2025-06-05 20:51:02.767298+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (73, 14, 'Vehicle id #116 successfully added', true, '2025-06-05 20:52:18.73082+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (74, 14, 'Vehicle id #117 successfully added', true, '2025-06-05 20:53:40.607016+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (80, 14, 'Vehicle id #119 successfully added', false, '2025-06-05 20:59:31.067008+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (75, 14, 'Driver id #38 successfully added', true, '2025-06-05 20:54:59.382915+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (77, 14, 'Driver id #40 successfully added', true, '2025-06-05 20:56:48.93798+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (78, 14, 'Vehicle id #117 was deleted', true, '2025-06-05 20:57:39.216076+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (76, 14, 'Driver id #39 successfully added', true, '2025-06-05 20:55:34.315925+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (79, 14, 'Vehicle id #118 successfully added', true, '2025-06-05 20:58:29.492284+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (81, 14, 'Driver id #41 successfully added', true, '2025-06-05 21:00:30.067593+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (82, 14, 'Driver id #42 successfully added', true, '2025-06-05 21:01:26.890451+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (83, 14, 'Driver id #43 successfully added', true, '2025-06-05 21:02:07.607346+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (84, 14, 'Driver id #42 was deleted', true, '2025-06-05 21:02:18.648113+00');
INSERT INTO public.notifications (id, user_id, message, is_read, created_at) VALUES (85, 14, 'Vehicle id #120 successfully added', true, '2025-06-05 21:03:38.007484+00');


--
-- TOC entry 3435 (class 0 OID 16583)
-- Dependencies: 218
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.users (id, name, surname, email, password) VALUES (14, 'Michał', 'Wszołek', 'michal6426@gmail.com', '$2y$10$cTW6smyK.FJH.cSuY9ESeeCC2iCRBTknpsDr47Wzk7nkKHGxOnIC6');


--
-- TOC entry 3445 (class 0 OID 16769)
-- Dependencies: 228
-- Data for Name: vehicle_location_history; Type: TABLE DATA; Schema: public; Owner: docker
--



--
-- TOC entry 3439 (class 0 OID 16607)
-- Dependencies: 222
-- Data for Name: vehicles; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.vehicles (id, user_id, brand, model, reg_number, mileage, vehicle_inspection_expiry, oc_ac_expiry, vin, avg_fuel_consumption, status, current_latitude, current_longitude, last_location_update, photo) VALUES (116, 14, 'Audi', 'A4', 'KWI2RD88', 86001, '2025-11-13', '2025-06-27', 'VSZZ657KK15956443', 9.4, 'available', 49.39890800, 18.75531500, '2025-06-05 20:52:18.627696+00', '2016-Audi-A4-render-front-three-quarter.jpg');
INSERT INTO public.vehicles (id, user_id, brand, model, reg_number, mileage, vehicle_inspection_expiry, oc_ac_expiry, vin, avg_fuel_consumption, status, current_latitude, current_longitude, last_location_update, photo) VALUES (120, 14, 'Skoda', 'Fabia', 'RJA77445', 254006, '2025-07-31', '2025-07-31', 'KJFF6573F85936174', 12.5, 'on_road', 50.30025000, 22.38663000, '2025-06-05 21:03:37.746813+00', 'fabia.png');
INSERT INTO public.vehicles (id, user_id, brand, model, reg_number, mileage, vehicle_inspection_expiry, oc_ac_expiry, vin, avg_fuel_consumption, status, current_latitude, current_longitude, last_location_update, photo) VALUES (121, 14, 'Fiat', '126p', 'KRA77445', 23009, '2025-10-25', '2025-10-23', 'GBCD6573F85936174', 6.9, 'in_service', 50.58253100, 15.88213100, '2025-06-14 10:38:49.344501+00', 'maluch.jpg');
INSERT INTO public.vehicles (id, user_id, brand, model, reg_number, mileage, vehicle_inspection_expiry, oc_ac_expiry, vin, avg_fuel_consumption, status, current_latitude, current_longitude, last_location_update, photo) VALUES (122, 14, 'Skoda', 'Fabia', 'KWA2235', 123000, '2025-09-25', '2025-10-21', 'KSCC65FGD85936174', 8, 'available', 49.50136800, 22.74365800, '2025-06-14 17:34:34.937165+00', '2016-Audi-A4-render-front-three-quarter.jpg');
INSERT INTO public.vehicles (id, user_id, brand, model, reg_number, mileage, vehicle_inspection_expiry, oc_ac_expiry, vin, avg_fuel_consumption, status, current_latitude, current_longitude, last_location_update, photo) VALUES (114, 14, 'Seat', 'Ibiza', 'KWI2296E', 166000, '2026-09-26', '2025-06-28', 'VSZZ6573F85936174', 11.2, 'in_service', 50.59567800, 17.38427800, '2025-06-05 20:49:43.136268+00', 'IMG_5542.JPG');
INSERT INTO public.vehicles (id, user_id, brand, model, reg_number, mileage, vehicle_inspection_expiry, oc_ac_expiry, vin, avg_fuel_consumption, status, current_latitude, current_longitude, last_location_update, photo) VALUES (115, 14, 'Porsche', '911', 'KR3FR41', 34, '2026-04-23', '2026-04-02', 'VGGG6573F85956443', 5.1, 'on_road', 49.99737100, 14.98005500, '2025-06-05 20:51:02.659202+00', 'porsche.jpg');
INSERT INTO public.vehicles (id, user_id, brand, model, reg_number, mileage, vehicle_inspection_expiry, oc_ac_expiry, vin, avg_fuel_consumption, status, current_latitude, current_longitude, last_location_update, photo) VALUES (118, 14, 'Suzuki', 'Swift', 'KNS1734M', 230000, '2026-02-06', '2026-09-25', 'VGGG657LKK5936174', 8.3, 'in_service', 53.11652800, 22.82126100, '2025-06-05 20:58:29.433959+00', 'cytrna.JPG');
INSERT INTO public.vehicles (id, user_id, brand, model, reg_number, mileage, vehicle_inspection_expiry, oc_ac_expiry, vin, avg_fuel_consumption, status, current_latitude, current_longitude, last_location_update, photo) VALUES (119, 14, 'Dodge', 'Challenger', 'KR4FM56', 142000, '2026-01-16', '2025-06-29', 'VLKK6573F85936174', 9.5, 'on_road', 49.81482600, 22.51056800, '2025-06-05 20:59:30.80517+00', 'dodge_challenger_srt_demon_animated_wallpaper_by_favorisxp_dhbog0v-fullview.jpg');


--
-- TOC entry 3457 (class 0 OID 0)
-- Dependencies: 223
-- Name: driver_vehicle_assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.driver_vehicle_assignments_id_seq', 44, true);


--
-- TOC entry 3458 (class 0 OID 0)
-- Dependencies: 219
-- Name: drivers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.drivers_id_seq', 43, true);


--
-- TOC entry 3459 (class 0 OID 0)
-- Dependencies: 225
-- Name: notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.notifications_id_seq', 151, true);


--
-- TOC entry 3460 (class 0 OID 0)
-- Dependencies: 217
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.users_id_seq', 17, true);


--
-- TOC entry 3461 (class 0 OID 0)
-- Dependencies: 227
-- Name: vehicle_location_history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.vehicle_location_history_id_seq', 1, false);


--
-- TOC entry 3462 (class 0 OID 0)
-- Dependencies: 221
-- Name: vehicles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.vehicles_id_seq', 122, true);


--
-- TOC entry 3275 (class 2606 OID 16740)
-- Name: driver_vehicle_assignments driver_vehicle_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.driver_vehicle_assignments
    ADD CONSTRAINT driver_vehicle_assignments_pkey PRIMARY KEY (id);


--
-- TOC entry 3267 (class 2606 OID 16600)
-- Name: drivers drivers_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.drivers
    ADD CONSTRAINT drivers_pkey PRIMARY KEY (id);


--
-- TOC entry 3277 (class 2606 OID 16761)
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- TOC entry 3263 (class 2606 OID 16592)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 3265 (class 2606 OID 16590)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 3279 (class 2606 OID 16777)
-- Name: vehicle_location_history vehicle_location_history_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.vehicle_location_history
    ADD CONSTRAINT vehicle_location_history_pkey PRIMARY KEY (id);


--
-- TOC entry 3269 (class 2606 OID 16619)
-- Name: vehicles vehicles_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_pkey PRIMARY KEY (id);


--
-- TOC entry 3271 (class 2606 OID 16621)
-- Name: vehicles vehicles_reg_number_key; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_reg_number_key UNIQUE (reg_number);


--
-- TOC entry 3273 (class 2606 OID 16623)
-- Name: vehicles vehicles_vin_key; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_vin_key UNIQUE (vin);


--
-- TOC entry 3282 (class 2606 OID 16741)
-- Name: driver_vehicle_assignments driver_vehicle_assignments_driver_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.driver_vehicle_assignments
    ADD CONSTRAINT driver_vehicle_assignments_driver_id_fkey FOREIGN KEY (driver_id) REFERENCES public.drivers(id) ON DELETE CASCADE;


--
-- TOC entry 3283 (class 2606 OID 16746)
-- Name: driver_vehicle_assignments driver_vehicle_assignments_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.driver_vehicle_assignments
    ADD CONSTRAINT driver_vehicle_assignments_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- TOC entry 3280 (class 2606 OID 16601)
-- Name: drivers drivers_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.drivers
    ADD CONSTRAINT drivers_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3284 (class 2606 OID 16762)
-- Name: notifications notifications_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- TOC entry 3285 (class 2606 OID 16778)
-- Name: vehicle_location_history vehicle_location_history_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.vehicle_location_history
    ADD CONSTRAINT vehicle_location_history_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- TOC entry 3281 (class 2606 OID 16624)
-- Name: vehicles vehicles_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


-- Completed on 2025-06-14 18:12:59 UTC

--
-- PostgreSQL database dump complete
--
