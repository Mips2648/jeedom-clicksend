
import inspect
import os
import re
from texte import Texte
import sys

class FichierSource(object):

    __fichiersSource = dict()
    __jeedomDir = ""

    # --- Les méthodes de static ---

    @classmethod
    def by_key (cls, key, create=True):
        if key in cls.__fichiersSource:
            return cls.__fichiersSource[key]
        if create:
            return cls(key)
        return None

    @classmethod
    def by_path (cls, path, create=True):
        relativ_path = cls.relativ_path(path)
        if relativ_path in cls.__fichiersSource:
            return cls.__fichiersSource[relativ_path]
        if create:
            return cls(relativ_path)
        return None

    @classmethod
    def fichiers_source (cls):
        fs = cls.__fichiersSource.values()
        return fs

    @staticmethod
    def relativ_path (path):
        return path.replace(os.environ.get("GITHUB_WORKSPACE"), "plugins/clicksend")

    @staticmethod
    def absolute_path (path):
        if path.startswith(os.environ.get("GITHUB_WORKSPACE")):
            return path
        return os.environ.get("GITHUB_WORKSPACE") + "/" + path

    # --- Les méthodes d'intance ---

    def __new__ (cls, path=""):
        if path == "":
            return None
        return super().__new__(cls)

    def __init__ (self, path):
        relativ_path = self.relativ_path(path)
        self.__relativ_path = relativ_path
        FichierSource.__fichiersSource[relativ_path] = self
        self.__textes = set()
        self.__textes_precedents = dict()

    def __del__ (self):
        del self.__fichiersSource[self.__relativ_path]

    def get_absolute_path(self):
        return self.absolute_path(self.__relativ_path)

    def get_relativ_path(self):
        return self.__relativ_path

    def add_texte(self, txt):
        self.__textes.add(txt)

    def add_texte_precedent(self, texte, traduction, langue):
        self.__textes_precedents.setdefault(langue,dict())
        self.__textes_precedents[langue][texte] = traduction

    def get_textes(self):
        return self.__textes

    def search_textes(self):
        patern___ = re.compile(r'__\s*\(\s*((?P<delim>["\'])(?P<texte>.*?)(?P=delim))\s*,\s*\S+\s*\)')
        try:
            with (open(self.get_absolute_path(), "r")) as f:
                content = f.read()
        except Exception as ex:
            info = inspect.currentframe()
            print (ex, "( at line" , info.f_lineno , ")")
            sys.exit(1)

        # Debug ("        Recherche {{..}}\n")
        for txt in re.findall("{{(.*?)}}",content):
            if len(txt) != 0:
                # Verbose ("        " + txt)
                self.__textes.add(Texte.by_texte(txt))
            else:
                Warning (f"ATTENTION, il y a un texte de longueur 0 dans le fichier <{self.__relativ_path}>")

        if self.__relativ_path[-4:] == ".php":
            # Debug ('        Recherche __("...",__FILE__)\n')
            for match in patern___.finditer(content):
                texte = match.group('texte')
                delim = match.group('delim')
                regex = r'(^' + delim + r')|([^\\]' + delim + r')'
                # Verbose ("        " + texte)
                if re.search(regex,texte):
                    print ("====  Délimineur de début et fin de chaîne trouvé dans le texte !!!")
                    print (f"      Fichier: {self.__relativ_path}")
                    print (f"      texte  : {texte}")
                else:
                    self.__textes.add(Texte.by_texte(texte))

    def get_traduction (self, langue):
        if (len(self.__textes) == 0):
            return None

        result = dict()
        for texte in self.__textes:
            if langue in self.__textes_precedents and texte.get_texte() in self.__textes_precedents[langue]:
                result[texte.get_texte()] = self.__textes_precedents[langue][texte.get_texte()]
            else:
                traduction = texte.get_traduction(langue)
                result[traduction[0]] = traduction[1]

        return result
