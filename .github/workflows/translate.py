import datetime
import json
import os
from fichierSource import FichierSource

# github_workspace = os.environ.get("GITHUB_WORKSPACE")

def get_all():
    base_dir = os.environ.get("GITHUB_WORKSPACE")
    for dir in ['core', 'desktop', 'plugin_info']:
        get_textes_from_source(f"{base_dir}/{dir}")

def get_textes_from_source(dir):
    print("Recherche de textes dans le code...")
    for root, dirs, files in os.walk(dir):
        print(root)
        for dirname in dirs:
            print(dirname)
            if dirname[0] == ".":
                dirs.remove(dirname)
            if (root.endswith("/core") and dirname == 'i18n'):
                dirs.remove(dirname)
            # for d in excludes:
            #     if fnmatch.fnmatch(dirname, d):
            #         dirs.remove(dirname)

        for fileName in files:
            # for f in excludes:
            #     if fnmatch.fnmatch(fileName, f):
            #         continue
            if fileName[-4:] == ".php" or fileName[-3:] == ".js":
            #   or fileName[-5:] == ".json"
            #   or fileName[-5:] == ".html"):
                absolute_path = f"{root}/{fileName}"
                print(f"    {absolute_path}...")
                fichier = FichierSource(f"{absolute_path}")
                fichier.search_textes()
    return

def write_traduction( textes ="" ):
    print("Ecriture du/des fichier(s) de traduction(s)...")
    langues = ['en_Us']
    for langue in langues:
        print(f"    Langue: {langue}...")

        fileName = os.environ.get("GITHUB_WORKSPACE")() + "/core/i18n"
        if (not os.path.exists (fileName)):
                os.mkdir(fileName)
        fileName = fileName + "/" + langue + ".json"

        # if backup:
        #     Verbose ("        Rotation des fichiers existants...")
        #     if os.path.exists (fileName + ".bck.5"):
        #         os.unlink(fileName + ".bck.5")

        #     for i in [4,3,2,1]:
        #         if os.path.exists (fileName + ".bck." + str(i)):
        #             os.rename (fileName + ".bck." + str(i), fileName + ".bck." + str(i+1))

        #     if os.path.exists (fileName + ".bck"):
        #         os.rename (fileName + ".bck" , fileName + ".bck.1")

        #     if os.path.exists (fileName):
        #         os.rename (fileName, fileName + ".bck")

        result = dict()
        for fs in FichierSource.fichiers_source():
            trad = fs.get_traduction(langue)
            if trad != None:
                result[fs.get_relativ_path()] = trad
        result['traduitjdm']={}
        result['traduitjdm']['version']='1'
        result['traduitjdm']['timestamp']=datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")

        with open(fileName, "w") as f:
            f.write(json.dumps(result, ensure_ascii=False, sort_keys = True, indent= indent).replace("/","\/"))

get_all()
write_traduction()