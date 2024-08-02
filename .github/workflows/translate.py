import os

# github_workspace = os.environ.get("GITHUB_WORKSPACE")

def get_all():
    base_dir = os.environ.get("GITHUB_WORKSPACE")
    for dir in ['core', 'desktop', 'plugin_info']:
        get_textes_from_source(f"{base_dir}/dir")

def get_textes_from_source(dir):
    print("Recherche de textes dans le code...")
    for root, dirs, files in os.walk(dir):
        print(root)
        for dirname in dirs:
            if dirname not in ['core', ]
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
            if ( fileName[-4:] == ".php"
              or fileName[-3:] == ".js"
              or fileName[-5:] == ".json"
              or fileName[-5:] == ".html"):
                absolute_path = f"{root}/{fileName}"
                print(f"    {absolute_path}...")
                # fichier = FichierSource(f"{absolute_path}")
                # fichier.search_textes()
    return

get_all()