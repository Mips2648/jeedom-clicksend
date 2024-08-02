import os

# github_workspace = os.environ.get("GITHUB_WORKSPACE")

def get_textes_from_source():
    print("Recherche de textes dans le code...")
    for root, dirs, files in os.walk(os.environ.get("GITHUB_WORKSPACE")):
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
            if ( fileName[-4:] == ".php"
              or fileName[-3:] == ".js"
              or fileName[-5:] == ".json"
              or fileName[-5:] == ".html"):
                absolute_path = f"{root}/{fileName}"
                print(f"    {absolute_path}...")
                # fichier = FichierSource(f"{absolute_path}")
                # fichier.search_textes()
    return

get_textes_from_source()