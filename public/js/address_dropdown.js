// This file contains the JavaScript functionality for the dynamic address dropdown system

// Data structure for Region 1 - Ilocos Region
const addressData = {
    "Region 1": {
        "La Union": {
            "Agoo": ["Ambitacay", "Balawarte", "Canada", "Capas", "Consolacion", "Macalva Central", "Macalva Norte", "Macalva Sur", "Nazareno", "Poblacion", "San Agustin East", "San Agustin Norte", "San Agustin West", "San Antonio", "San Jose", "San Julian Central", "San Julian East", "San Julian Norte", "San Julian West", "San Manuel Norte", "San Manuel Sur", "San Marcos", "San Miguel", "San Nicolas East", "San Nicolas Norte", "San Nicolas Sur", "San Nicolas West", "San Roque East", "San Roque West", "Santa Ana", "Santa Barbara", "Santa Fe", "Santa Maria", "Santa Monica", "Santa Rita"],
            "Aringay": ["Alaska", "Dulao", "Gallano", "Macabato", "Pangao-aoan East", "Pangao-aoan West", "Poblacion", "San Antonio", "San Benito Norte", "San Benito Sur", "San Eugenio", "San Juan", "San Simon East", "San Simon West", "Santa Cecilia", "Santa Lucia", "Santa Rita East", "Santa Rita West"],
            "Bacnotan": ["Agtipal", "Arosip", "Bacnotan Proper", "Bani", "Baroro", "Buangan", "Bulala", "Cabaroan", "Cabarsican", "Casiaman", "Cot-cot", "Duplas", "Gabao", "Liguis", "Maragayap", "Nagsabaran", "Nangalisan", "Nipa", "Ortega", "Pang-pang", "Pangdan", "Poblacion", "Quirino", "Rabon", "Salincob", "San Martin", "Santa Rita", "Sapilang", "Sayoan", "Sipulo", "Tammocalao", "Ubbog"],
            "Bagulin": ["Alibangsay", "Baay", "Cambaly", "Dagup", "Suyo", "Tagudtud", "Tio-angan"],
            "Balaoan": ["Almeida", "Alzate", "Antonino", "Apatot", "Ar-arampang", "Baracbac Este", "Baracbac Oeste", "Bet-tagan", "Bulbulala", "Butubut Este", "Butubut Norte", "Butubut Oeste", "Butubut Sur", "Cabuaan", "Calliat", "Camiling", "Calungbuyan", "Dr. Pedro T. Orata", "Guinabang", "Matalava", "Nagsabaran Este", "Nagsabaran Norte", "Nagsabaran Oeste", "Nagsabaran Sur", "Nalasin", "Pagbennecan", "Pagleddegan", "Pagn-atan", "Pantar Norte", "Pantar Sur", "Paraoir", "Patpata I", "Patpata II", "Sablut", "San Pablo", "Sinapangan Norte", "Sinapangan Sur", "Sumalig", "Wallace"],
            "Bangar": ["Agdeppa", "Alzate", "Bangaoil", "Barraca", "Caggao", "Cadapli", "Campo", "Gabut Norte", "Gabut Sur", "General Prim Norte", "General Prim Sur", "General Terrero", "Lioac Norte", "Lioac Sur", "Lubong Buaya", "Mindoro", "Monge", "Nagsicaan", "Paratong Norte", "Paratong Sur", "Poblacion Norte", "Quintarong", "Reyna Regente", "Ribsuan", "Santa Rosa", "Sinapangan Norte", "Sinapangan Sur", "Talogtog"],
            "Bauang": ["Bagbag", "Balaoan", "Bawanta", "Bucayab", "Central East", "Central West", "Disso-or", "Lower Santiago", "Nagrebcan", "Pagdalagan Sur", "Pagleddegan", "Palapar", "Palugsi-an", "Parian Este", "Parian Oeste", "Payocpoc Norte Este", "Payocpoc Norte Oeste", "Payocpoc Sur", "Pilar", "Pudoc", "Quinavite", "Santa Monica", "Santiago", "Taberna", "Upper Santiago"],
            "Burgos": ["Agpay", "Bench Mark", "Bilis", "Delles", "Libtong", "Lower Cadanglaan", "New Poblacion", "Paagan", "Upper Cadanglaan"],
            "Caba": ["Bato", "Gana", "Liquicia", "Poblacion Norte", "Poblacion Sur", "San Carlos", "San Cornelio", "Santiago", "Sobredillo", "Urayong", "Wenceslao"],
            "Luna": ["Alcala", "Barangobong", "Cantoria", "Darigayos", "Nalvo Norte", "Nalvo Sur", "Rimos", "Rissing", "Salcedo", "Santo Domingo", "Sucoc Norte", "Sucoc Sur", "Sucoc Tres", "Victoria"],
            "Naguilian": ["Al-alinao Norte", "Al-alinao Sur", "Ambaracao Norte", "Ambaracao Sur", "Angin", "Balecbec", "Bariquir", "Barraca", "Bato", "Cabaritan", "Casilagan", "Dal-lipaoen", "Daramuangan", "Guesset", "Gusing Norte", "Gusing Sur", "Imelda", "Lioac", "Magungunay", "Nagsidangan", "Natividad", "Ortiz", "Ribsuan", "San Antonio", "San Blas", "San Isidro", "Suguidan Norte", "Suguidan Sur", "Tuddingan"],
            "Pugo": ["Ambalite", "Amistad", "Cares", "Cuenca", "Duplas", "Maoasoas", "Palcit", "Poblacion East", "Poblacion West", "San Luis"],
            "Rosario": ["Agat", "Alipang", "Ambangonan", "Bani", "Bantugo", "Bimmuega", "Birunget", "Cabaruan", "Cadumanian", "Camp One", "Carunuan East", "Cataguingan", "Cataguingan-Acao", "Consuegra", "Gumot-Nagcolaran", "Inabaan Norte", "Inabaan Sur", "Nadsaag", "Nagyubuyuban", "Parasapas", "Poblacion East", "Poblacion West", "Raois", "San Jose", "Tayaoan", "Vila", "Vila Cervantes"],
            "San Fernando City": ["Bacsil", "Bangbangolan", "Bangcusay", "Biday", "Cadaclan", "Cadaratan", "Canaoay", "Carlatan", "Catbangen", "Dalumpinas Este", "Dalumpinas Oeste", "Ilocanos Norte", "Ilocanos Sur", "Langcuas", "Lingsat", "Madayegdeg", "Mameltac", "Nagguilian", "Namtutan", "Pagdaraoan", "Pagudpud", "Parian", "Poro", "Puspus", "Sto. Tomas", "San Agustin", "San Francisco", "San Juan", "Sevilla", "Tanqui", "Taboc"],
            "San Gabriel": ["Amontay", "Apayao", "Balbalayang", "Bayabas", "Bucao", "Dalioapoan", "Gallano", "Lacong", "Lipay Este", "Lipay Norte", "Lipay Punsal", "Lipay Proper", "Lipay Sur", "Mundo", "Poblacion", "Polipol", "Bumbuneg", "San Antonio", "Schmidt"],
            "San Juan": ["Allangigan", "Bacsil", "Balballosa", "Bangcusay", "Bulbulala", "Caarusipan", "Cabuquiran", "Cabuquiran Norte", "Casilagan", "Calublub", "Daramuangan", "Dasay", "Dinanum", "Duplas", "Gabao-an", "Guinabang", "Ili Norte", "Ili Sur", "Lanas", "Nadsaag", "Nagsaag", "Naguirangan", "Nagyubuyuban", "Pacpacac", "Pandan", "Quitazen", "Santa Filomena", "Sub-Urban", "Taboc", "Urbiztondo", "White Beach"],
            "Santol": ["Lettac Norte", "Lettac Sur", "Mangaan", "Paagan", "Poblacion", "Puguil", "Ramot", "Sasaba", "Tubaday"],
            "Sudipen": ["Bangar", "Castro", "Duplas", "Ipet", "Maliclico", "Old Central", "Poblacion", "Seng-ngat", "Turod"],
            "Tubao": ["Anduyan", "Amallapay", "Bauan", "Caoigue", "Cogcogin", "Francia South", "Francia West", "Halog East", "Halog West", "Leones", "Lloren", "Magsaysay", "Mapalad", "Pideg", "Pilpila", "Poblacion", "Rizal", "Sabangan", "San Pascual", "Santa Teresa", "Sugpon", "Tacmay", "Tuboc"]
        },
          "Ilocos Norte": {
            "Adams": ["Adams", "Bucarot", "Lingsat"],
            "Bacarra": ["Bani", "Buyon", "Cabaruan", "Calioet-Libong", "Cabulalaan", "Cabusligan", "Casilian", "Corocor", "Duripes", "Libtong", "Macupit", "Nambaran", "Natba", "Pabolbolon", "Pagpaguilid", "Paninaan", "Pasiokan", "Pipias", "Poblacion 1", "Poblacion 2", "Pulangi", "San Andres I", "San Andres II", "San Antonio", "San Joaquin", "San Pablo", "San Pedro", "San Roque", "San Simon", "San Vicente", "Santa Filomena", "Santa Rita", "Teppang", "Tubburan"],
            "Badoc": ["Aring", "Gabut Norte", "Gabut Sur", "Labut", "Maabay", "Napu", "Paltit", "Parangopong", "Poblacion 1", "Poblacion 2", "San Julian", "San Pedro", "Santa Cruz", "Saud", "Sesnep"],
            "Bangui": ["Abaca", "Bacsil", "Banban", "Baruyen", "Dadaor", "Lanao", "Malasin", "Manayon", "Masikil", "Nagbalagan", "Pasuquin", "Payac", "Taguiporo", "Teppeng", "Utol"],
            "Banna": ["Bangsar", "Barbarangay", "Bugayong", "Caestebanan", "Caribquib", "Crispina", "Dumalneg", "Guerrero", "Mabanbanag", "Macayepyep", "Magnuang", "Nagpatpatan", "Rang-ay", "Sinamar", "Tabtabagan", "Valdez"],
            "Batac": ["Ablan", "Acosta", "Aglipay", "Baay", "Baligat", "Barani", "Barit", "Ben-agan", "Bungon", "Cangrunaan", "Camandingan", "Capacuan", "Caunayan", "Cayasan", "Alabaan", "Baoa", "Bil-loca", "Biningan", "Colo", "Dariwdiw", "Lacub", "Nagbacalan", "Naguirangan", "Palongpong", "Parangopong", "Payao", "Quiling Norte", "Quiling Sur", "Ricarte", "Pimentel", "Quiom", "Rayuray", "Sagsagat", "Palacapaca", "San Julian", "San Matias", "San Pedro", "Suyo", "Tabug", "Valdez", "Bungcag", "Sumader"],
            "Burgos": ["Ablan", "Agaga", "Bayog", "Bobon", "Buduan", "Nagsurot", "Paayas", "Pagali", "Poblacion", "Saoit"],
            "Carasi": ["Angset", "Barbaqueso", "Sampituhan", "Santiago"],
            "Currimao": ["Anggapang", "Bimmanga", "Cabuusan", "Lang-ayan", "Lioes", "Maglaoi Centro", "Maglaoi Norte", "Maglaoi Sur", "Pias Norte", "Pias Sur", "Pilar", "Poblacion 1", "Poblacion 2", "Salugan", "San Simeon", "Santa Cruz", "Torre", "Victoria", "Wawa"],
            "Dingras": ["Barong", "Bungcag", "Cajacaren", "Cali", "Capasan", "Dancel", "Espiritu", "Foz", "Gabon", "Laoa", "Lanas", "Loing", "Madamba", "Madriño", "Mandaloque", "Nagbettedan", "Nagtutundan", "Paoay", "Parado", "Peralta", "Poblacion", "Puruganan", "Ragas", "Quiling", "Sacritan", "Sagpatan", "San Esteban", "San Jose", "San Juan", "San Marcelino", "San Nicolas", "San Marcos", "Santa Filomena", "Suyo"],
            "Dumalneg": ["Cabaritan", "Kalaw"],
            "Laoag City": ["Araniw", "Bacsil", "Balatong", "Balacad", "Barit-Pandan", "Bengcag", "Buttong", "Cabungaan", "Caaoacan", "Calayab", "Camangaan", "Casili", "Cataban", "Cavit", "Dibua", "Gabu", "Lagui-Sail", "La Paz", "Lataag", "Lubing", "Nalbo", "Navotas", "Nangalisan", "Pila", "Samtoga", "San Bernardo", "San Fernando", "San Guillermo", "San Isidro", "San Jacinto", "San Lorenzo", "San Marcelino", "San Mateo", "San Matias", "San Miguel", "San Pedro", "San Quirino", "Santa Angela", "Santa Joaquina", "Santa Maria", "Santa Rosa", "Suba-Paoay", "Suyo", "Talingaan", "Tangid", "Vira", "Zamboanga"],
            "Marcos": ["Daquioag", "Elizabeth", "Escoda", "Fortuna", "Imelda", "Lydia", "Mabuti", "Malasin", "Pacifico", "Ragas", "Santiago", "Ferdinand E. Marcos"],
            "Nueva Era": ["Acnam", "Barangobong", "Barikir", "Bulbulala", "Cabittauran", "Caray", "Garnaden", "Lumbaan-Bicbica", "Naguillan", "Poblacion", "Santo Niño", "Uguis"],
            "Pagudpud": ["Aggasi", "Baduang", "Balaoi", "Burayoc", "Caunayan", "Dampig", "Ligaya", "Pancian", "Pasaleng", "Poblacion 1", "Poblacion 2", "Saguigui", "Saud", "Subec", "Tarrag", "Caparispisan"],
            "Paoay": ["Bacsil", "Balacad", "Cabagoan", "Cabangaran", "Callaguip", "Cayasan", "Dolores", "Laoa", "Monte", "Mumulaan", "Nagbacalan", "Nalasin", "Nanguyudan", "Pasil", "Paratong", "Poblacion 1", "Poblacion 2", "San Agustin", "San Blas", "San Juan", "San Pedro", "San Roque", "Santa Rita", "Suba", "Sungadan", "Veronica"],
            "Pasuquin": ["Apatut", "Batuli", "Batalla", "Bengbeng", "Bungro", "Carusipan", "Caparispisan", "Davila", "Dilanis", "Dilavo", "Estancia", "Naglicuan", "Ngabangab", "Poblacion 1", "Poblacion 2", "Poblacion 3", "Poblacion 4", "Pragata", "Puyupuyan", "Sulbec", "Salpad", "San Juan", "Santa Catalina", "Santa Matilde", "Surong", "Susugaen", "Tabungao"],
            "Piddig": ["Ab-abut", "Anao", "Arua-ay", "Braw", "Boyboy", "Cabangaran", "Cabaritan", "Calambeg", "Calipayan", "Calimugtong", "Capariaan", "Estancia", "Lagandit", "Lawigawen", "Loing", "Libnaoan", "Maab-abaca", "Mangitayag", "Maruaya", "Parparia", "Poblacion 1", "Poblacion 2", "San Antonio", "Santa Maria", "Sucsuquen", "Tangaoan", "Tonoton"],
            "Pinili": ["Aglipay", "Apatut-Lubong", "Badio", "Barbar", "Bulbulala", "Burgos", "Busan", "Capangdanan", "Dalayap", "Darat", "Gulpeng", "Liliputen", "Lumbaan-Bicbica", "Nagtrigoan", "Pintian", "Poblacion 1", "Poblacion 2", "Pugaoan", "Sacritan", "Sagpatan", "Salanap", "Santo Tomas"],
            "San Nicolas": ["Baay", "Babasit", "Bagbag", "Bangsirit", "Bingao", "Bugnay", "Bulnay", "Caaoacan", "Catangraran", "Daramuangan", "Foz", "Garnaden", "Imelda", "Lapaz", "Mabaldeg", "Nagbacalan", "Nambaran", "Pagsanahan", "Palestina", "Pias", "Poblacion 1", "Poblacion 2", "Poblacion 3", "Poblacion 4", "Saludares", "San Francisco", "San Marcos", "San Pablo", "San Silvestre", "Santa Monica", "Santo Niño", "Victory"],
            "Sarrat": ["Bagbag", "Binaratan", "Cabuloan", "Cayambanan", "Daquioag", "Golgol", "Gusing", "Macanaya", "Madiladig", "Maggiaiag", "Nagrebcan", "Naell", "Pang-pang", "Parut", "Poblacion 1", "Poblacion 2", "Poblacion 3", "Poblacion 4", "Poblacion 5", "Rivadavia", "Ruiz", "Sagpatan", "St. Michael", "San Agustin", "San Bernabe", "San Joaquin", "San Jose", "San Miguel", "San Nicolas", "Santa Rosa", "Tartarabang", "Tangid"],
            "Solsona": ["Aguitap", "Bagbag", "Bagbago", "Barcelona", "Battiang", "Bubuos", "Capurictan", "Catangraran", "Cacafean", "Darapdap", "Juan", "Laureta", "Lipay", "Maananteng", "Mabuti", "Manalpac", "Manpatac", "Nalasin", "Nagpatpatan", "Paltit", "Poblacion", "Sagpatan", "San Julian", "San Juan", "Santa Ana", "Santiago", "Santo Niño"],
            "Vintar": ["Abkir", "Alejo Malasig", "Bulbulala", "Cabinuangan", "Cabua-an", "Canaam", "Dagupan", "Dipilat", "Esperanza", "Ferdinand", "Isic-Isic", "Lubnac", "Mabanbanag", "Malampa", "Manarang", "Margaay", "Namoroc", "Parparoroc", "Parut", "Poblacion Norte", "Poblacion Sur", "Sagpatan", "San Jose", "San Nicolas", "San Pedro", "San Ramon", "San Roque", "Santa Maria", "Santa Visitacion", "Tamdagan", "Visaya", "Ester"]
        },
        "Ilocos Sur": {
            "Alilem": ["Alilem Daya", "Alilem Laud", "Anaao", "Apang", "Apaya", "Atabao", "Batbato", "Cagayungan", "Dalawa", "Kiat", "Man-atong", "Pattaoig", "Pudpudil", "Poblacion", "Sapid"],
            "Banayoyo": ["Bagbagutot", "Banbanaal", "Bisangol", "Cadanglaan", "Cardona", "Elefante", "Linganay-Casilagan", "Naguimba", "Pila", "Poblacion", "Lopez", "Villa"],
            "Bantay": ["Aggay", "Balaleng", "Banaoang", "Bulag", "Cabalangegan", "Cabuloan", "Cabusligan", "Capangpangan", "Guimod", "Lingsat", "Mabilbila Grande", "Mabilbila Sur", "Malingeb", "Manangat", "Ora", "Paing", "Poblacion", "Pudoc", "Quimmallogong", "Sagneb", "San Andres", "San Isidro", "San Mariano", "San Marcelino", "Sinabaan", "Taguiporo", "Taleb"],
            "Burgos": ["Ambugao", "Bangbangar", "Dayanki", "Macaoayan Norte", "Macaoayan Sur", "Patac", "Poblacion", "Subadi"],
            "Cabugao": ["Alinaay", "Arwas", "Baclig", "Barangobong", "Bato", "Bonifacio", "Cabaritan", "Cacadiran", "Dardarat", "Daclapan", "Lipit", "Maradodon", "Margaay", "Namruangan", "Naputangan", "Pila", "Poblacion", "Quinfermin", "Quinapon", "Reppaac", "Salapasap", "Salomague", "Sisim", "Turod"],
            "Candon City": ["Allangigan Primer", "Allangigan Segundo", "Amguid", "Ayudante", "Bagani Campo", "Bagani Gabor", "Bagani Tocgo", "Bagani Ubbog", "Bagar", "Balingaoan", "Calungboyan", "Caterman", "Cubcubbuot", "Darapidap", "Langlangca", "Lingsat", "Oaig Daya", "Oaig-Laud", "Paypayad", "Parioc Primero", "Parioc Segundo", "Patpata Primer", "Patpata Segundo", "Paypayad", "Poblacion"],
            "Caoayan": ["Anonang Mayor", "Anonang Menor", "Bonifacio", "Caparacadan", "Fuerte", "Manangat", "Nansuagao", "Pantay Tamurong", "Poblacion Norte", "Poblacion Sur", "Puro", "Villamar"],
            "Cervantes": ["Aluling", "Comillas North", "Comillas South", "Concepcion", "Dinwede East", "Dinwede West", "Libang", "Maitic", "Pilipil", "Poblacion", "Remedios", "Rosario", "San Juan", "San Luis"],
            "Galimuyod": ["Abaya", "Baracbac", "Bidbiday", "Calungboyan", "Calimugtong", "Daldagan", "Directo", "Kilang", "Legaspi", "Mabayag", "Mckinley", "Nagsingcaoan", "Oaoan Paquito", "Patac", "Poblacion", "Rubio", "Sacpil", "Sapang", "Sinabaan"],
            "Gregorio del Pilar": ["Alfonso", "Bussot", "Concepcion", "Dapdappig", "Langiden", "Poblacion", "San Cristobal"],
            "Lidlidda": ["Banucal", "Bequi-Walin", "Bugui", "Calungbuyan", "Carcarabasa", "Suysuyan", "Poblacion", "San Vicente"],
            "Magsingal": ["Alangan", "Bacar", "Barbarit", "Bungro", "Cabaroan", "Cadanglaan", "Dagup", "Labut", "Maas-asin", "Macatcatud", "Manzante", "Maratudo", "Miramar", "Namalpalan", "Napo", "Pagsanaan", "Panay", "Paratong", "Patong", "Puro", "San Basilio", "San Clemente", "San Julian Norte", "San Julian Sur", "San Ramon", "San Vicente", "Santa Catalina", "Santa Monica"],
            "Nagbukel": ["Bandril", "Bantugo", "Cadanglaan", "Casilagan", "Lapting", "Mission", "Poblacion East", "Poblacion West"],
            "Narvacan": ["Ambalite", "Aquib", "Arangin", "Bulanos", "Cabalangegan", "Cadacad", "Cagayungan", "Camarao", "Claro", "Dasay", "Dinalaoan", "Estancia", "Lungog", "Margaay", "Mora", "Nanguneg", "Pantoc", "Paratong", "Parparia", "Quinarayan", "Rivera", "San Antonio", "San Jose", "San Pablo", "San Pedro", "Santa Lucia", "Sucoc", "Sulvec", "Turod"],
            "Quirino": ["Banoen", "Cayus", "Lamag", "Langaoan", "Malideg", "Namitpit", "Patiacan", "Poblacion East", "Poblacion West", "Suagayan"],
            "Salcedo": ["Atabay", "Balugang", "Buliclic", "Butarog", "Butigui", "Dacandanan", "Madarang", "Naguilian", "Padaoil", "Poblacion", "Pangio", "San Gaspar", "Sinalaban"],
            "San Emilio": ["Cabaroan", "Kalumsing", "Lancuas", "Matibuey", "Paltoc", "Poblacion", "San Miliano", "Sibsibbu", "Taleb", "Tiagan"],
            "San Esteban": ["Apatot", "Bateria", "Cabaroan", "Cappa-angan", "Caroan", "Casili", "Katipunan", "Poblacion", "San Nicolas", "San Pablo", "San Rafael", "Santiago"],
            "San Ildefonso": ["Balaoc", "Buli", "Gongogong", "Iboy", "Otol", "Poblacion", "Polong Norte", "Polong Sur", "San Fracisco", "Sapriana"],
            "San Juan": ["Asilang", "Bannuar", "Barbar", "Cacandongan", "Camanggaan", "Camindoroan", "Caronoan", "Dapa", "Duplas", "Immayos Norte", "Immayos Sur", "Lapog Norte", "Lapog Sur", "Lapting", "Malammin", "Namnama", "Pipingdan", "Poblacion", "Quitaguin", "Refugio", "Saoang", "Santo Tomas", "Solotsolot", "Tinggui-an", "Tuliao", "Udiao"],
            "San Vicente": ["Bantay", "Basang", "Bayubay Norte", "Bayubay Sur", "Bugnay", "Lubong", "Poblacion"],
            "Santa": ["Bucalag", "Cabangaran", "Calungboyan", "Casiber", "Dammay", "Labut", "Magsaysay", "Manueva", "Marcos", "Nagtupacan", "Namalangan", "Oribi", "Pasungol", "Puspus", "Quezon", "Rancho", "Tabucolan", "Sacuyya Norte", "Sacuyya Sur"],
            "Santa Catalina": ["Ampandula", "Banaoang", "Bungro", "Cabaroan", "Cabittaogan", "Cabuloan", "Gabor Norte", "Gabor Sur", "Lagpitao", "Lesteb", "Magsaysay", "Matanubong", "Nagpanaoan", "Poblacion Norte", "Poblacion Sur", "Sabang", "Sinabaan", "Sinait", "Tamorong"],
        },
        "Pangasinan": {}
    }
};

// Initialize the selections when the document loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the region dropdown with Region 1
    const regionSelect = document.getElementById('region');
    regionSelect.innerHTML = '<option value="">Select region</option>';
    regionSelect.innerHTML += '<option value="Region 1" selected>Region 1 - Ilocos Region</option>';
    
    // Trigger change event to populate provinces
    regionChange();

    // Add event listeners
    regionSelect.addEventListener('change', regionChange);
    document.getElementById('province').addEventListener('change', provinceChange);
    document.getElementById('municipality').addEventListener('change', municipalityChange);
});

// Function to handle region change
function regionChange() {
    const region = document.getElementById('region').value;
    const provinceSelect = document.getElementById('province');
    const municipalitySelect = document.getElementById('municipality');
    const barangaySelect = document.getElementById('barangay');
    
    // Clear all dropdowns below region
    provinceSelect.innerHTML = '<option value="">Select province</option>';
    municipalitySelect.innerHTML = '<option value="">Select municipality</option>';
    barangaySelect.innerHTML = '<option value="">Select barangay</option>';
    
    // If a region is selected, populate the provinces
    if (region && addressData[region]) {
        const provinces = Object.keys(addressData[region]);
        provinces.forEach(province => {
            const option = document.createElement('option');
            option.value = province;
            option.textContent = province;
            // Set La Union as default selected
            if (province === 'La Union') {
                option.selected = true;
            }
            provinceSelect.appendChild(option);
        });
        
        // Trigger province change to load municipalities for La Union
        provinceChange();
    }
}

// Function to handle province change
function provinceChange() {
    const region = document.getElementById('region').value;
    const province = document.getElementById('province').value;
    const municipalitySelect = document.getElementById('municipality');
    const barangaySelect = document.getElementById('barangay');
    
    // Clear all dropdowns below province
    municipalitySelect.innerHTML = '<option value="">Select municipality</option>';
    barangaySelect.innerHTML = '<option value="">Select barangay</option>';
    
    // If a province is selected, populate the municipalities
    if (region && province && addressData[region][province]) {
        const municipalities = Object.keys(addressData[region][province]);
        municipalities.forEach(municipality => {
            const option = document.createElement('option');
            option.value = municipality;
            option.textContent = municipality;
            municipalitySelect.appendChild(option);
        });
    }
}

// Function to handle municipality change
function municipalityChange() {
    const region = document.getElementById('region').value;
    const province = document.getElementById('province').value;
    const municipality = document.getElementById('municipality').value;
    const barangaySelect = document.getElementById('barangay');
    
    // Clear barangay dropdown
    barangaySelect.innerHTML = '<option value="">Select barangay</option>';
    
    // If a municipality is selected, populate the barangays
    if (region && province && municipality && addressData[region][province][municipality]) {
        const barangays = addressData[region][province][municipality];
        barangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay;
            option.textContent = barangay;
            barangaySelect.appendChild(option);
        });
    }
}