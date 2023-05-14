import sys
import os
import shutil
import threading
import time
import urllib.parse as up
import util
import pathlib
import mysql.connector
import logging
import traceback


try:   
    logging.basicConfig(filename='browser.log', level=logging.DEBUG, filemode="a")
    # raise Exception 
    start = time.time()
    def pre():
        # Presorting of input
        order = sys.argv[1:]
        order = {"order": order[0],
                "path": order[1]}
        return order
    def move(path):
        t = threading.Thread(target=__move, kwargs={"path": path})
        t.start()
    def __move(path):
        path = up.unquote(path)
        values = path.split("+")
        filename, new_path = values[0], values[1]
        directory = str(pathlib.Path(__file__).parent.absolute())
        directory = directory.split("\\")
        directory = "/".join(directory[:-1]) + "/static/"
        if filename.startswith("Browser"):
            filename = util.unix_dir(os.path.abspath(os.path.join(os.getcwd(), "static", filename)))
        logging.info("Accessing filepath %s" % filename)
        while new_path[0] == " ":
            new_path = new_path[1:]
        try:
            shutil.move(filename, os.path.join(directory, new_path))
            logging.info("Moving file {} to path {}.".format(os.path.split(filename)[1], new_path))
        except Exception as e:
            print(e)
            logging.error("{} in 'move'.".format(e))
    def new_dir(path):
        try:
            os.mkdir(up.unquote_plus(path)) 
        except FileExistsError as e:
            print(e)
        print("success")
    def read_db():
        mydb = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="browser"
        )
        mycursor = mydb.cursor()
        mycursor.execute("SELECT id, path FROM objects")
        myresult = mycursor.fetchall()
        mycursor.close()
        return [i[1] for i in myresult]
    def get_db_index(path):
        mydb = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="browser"
        )
        path = path.replace("\\", "/")
        mycursor = mydb.cursor()
        mycursor.execute(f"SELECT id, path FROM objects WHERE path LIKE '{path}%'")
        myresult = mycursor.fetchall()
        mycursor.close()
        return {path: di for di, path in myresult}
    def read_gallery(ret=None):
        mydb = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="browser"
        )
        mycursor = mydb.cursor()
        mycursor.execute("SELECT id, path, folder FROM gallery")
        myresult = mycursor.fetchall()
        mycursor.close()
        if ret is not None:
            for i in myresult:
                if i[1] == ret:
                    return i[0]
        return {i[0]: i[1] for i in myresult}
    def update_databse(file, new_path, table):
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="browser"
        )
    param = pre()
    logging.info("Executing order '{}'".format(param["order"]))
    # param = {"order": "upload"}
    if param["order"] == "new_dir":
        new_dir(param["path"])
    elif param["order"] == "rename":
        param["path"] = up.unquote_plus(os.path.abspath(param["path"]).replace("%20", " ").replace("\\", "/").replace("Images", "static"))
        param["path"] = param["path"].split(", ")
        upon = param["path"][1].split("/")[:-1]
        param["path"][0] = "/".join(upon)
        db = mysql.connector.connect(
                    host="localhost",
                    user="root",
                    password="",
                    database="browser"
        )
        mycursor = db.cursor()
        old, new = param["path"][1], param["path"][0] + "/" + param["path"][2]
        rel_old, rel_new = old[old.find("Browser"):], new[new.find("Browser"):]
        util.rename(rel_old, rel_new)
        try:
            os.rename(param["path"][1], param["path"][0] + "/" + param["path"][2])
        except FileExistsError:
            pass
        print("success")
    elif param["order"] == "delete":
        param["path"] = up.unquote_plus(os.path.abspath(param["path"]).replace("%20", " ").replace("\\", "/").replace("Images", "static"))
        for r, _, _ in os.walk(param["path"]):
            if len(os.listdir(r)) == 0:
                util.delete(r)
                try:
                    if os.path.isdir(r):
                        shutil.rmtree(r)
                    else:
                        os.remove(r)
                except FileNotFoundError:
                    pass
        print("success")
    elif param["order"] == "upload":
        try:
            param["path"] = up.unquote_plus(param["path"])
            param["path"] = param["path"].replace("\\", "/")
            param["path"] = param["path"].replace("//", "/")
            param["path"] = param["path"].split(", ")
            if not os.path.exists(os.path.join(os.getcwd(), "NEW")):
                os.mkdir(os.path.join(os.getcwd(), "NEW"))
            util.move(param["path"][1], os.path.join(os.getcwd(), "NEW"))
            util.clear(os.path.join(os.getcwd(), "NEW"))
            n = util.duplicate(os.path.join(os.getcwd(), "NEW"))
            logging.info("Deleted {} duplicate files".format(n))
            util.move(os.path.join(os.getcwd(), "NEW"), param["path"][0])
            shutil.rmtree(os.path.join(os.getcwd(), "NEW"))
            n = util.check_duplicates(param["path"][0])
            logging.info("Deleted {} duplicate files".format(n))
            print("success")
        except Exception as e:
            print(e)
    elif param["order"] == "move":
        path = up.unquote(os.path.abspath(param["path"]).replace("%20", " "))
        path = path.replace("\\", "/").replace("Images", "static")
        move(path)
        print("success")
    elif param["order"] == "move_dir_dirs":
        r = param["path"].split("+")
        path, target = r[0], r[-1]
        logging.info("Accessing filepath %s" % target)
        if r[0] in r[-1]:
            raise ValueError("A directory can't be moved to its sub-directory.")
        path = up.unquote(os.path.abspath(path).replace("%20", " "))
        path = path.replace("\\", "/").replace("Images", "static")
        target = up.unquote(os.path.abspath(target).replace("%20", " "))
        target = target.replace("\\", "/").replace("Images", "static")
        diff = ""
        for i in range(len(path)):
            if i >= len(target) or path[i] != target[i]:
                break
            diff += path[i]
        if target.find("/ Browser") != -1:
            target = target.replace("/ Browser", "/Browser")
        if not os.path.exists(target+"/"+path.split("/")[-1]):
            os.mkdir(target+"/"+path.split("/")[-1])   
        target = target+"/"+path.split("/")[-1]
        for r, d, f in os.walk(path):
            for directory in d:
                g = r + "/" + directory
                g = g.replace("\\", "/")
                section = g.replace(path, "")
                nd = target + section
                if not os.path.exists(nd):
                    os.mkdir(nd)
        print("success")
    elif param["order"] == "move_dir_files":
        mydb = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="browser"
        )
        mycursor = mydb.cursor()
        path, target = param["path"].split("+")
        logging.info("Accessing filepath %s" % path)
        path = up.unquote_plus(os.path.abspath(path).replace("%20", " "))
        path = path.replace("\\", "/").replace("Images", "static")
        target = up.unquote_plus(os.path.abspath(target).replace("%20", " "))
        target = target.replace("\\", "/").replace("Images", "static")
        target += "/"+path.split("/")[-1]
        diff = ""
        for i in range(len(path)):
            if i >= len(target) or path[i] != target[i]:
                break
            diff += path[i]
        gallery = read_gallery()
        for r, d, f in os.walk(path):
            path_ids = get_db_index(r[r.find("Browser"):])
            for file in f:
                g = r + "/" + file
                g = g.replace("\\", "/")
                section = g.replace(path, "")
                old_file = path + section
                old_file = old_file[old_file.find("Browser"):]
                g = r
                g = g.replace("\\", "/")
                section = g.replace(path, "")
                new_file = target + section
                if not os.path.exists(os.path.split(new_file)[0]):
                    os.mkdir(os.path.split(new_file)[0])
                new_file = new_file[new_file.find("Browser"):]
                if not old_file.endswith(".jpg"):
                    continue
                try:
                    id = path_ids[old_file]
                except KeyError:
                    continue
                name = new_file+"/"+old_file.split("/")[len(old_file.split("/"))-1]
                sql = "UPDATE objects SET PATH = %s WHERE ID = %s"
                val = (str(name), str(id))
                mycursor.execute(sql, val)
                mydb.commit()
                if old_file in gallery:
                    di = gallery[old_file]
                    sql = "UPDATE gallery SET PATH = %s WHERE ID = %s"
                    val = (str(name), str(di))
                    mycursor.execute(sql, val)
                    mydb.commit()
                logging.info("Accessing filepath %s" % old_file)
                move(old_file+"+"+new_file)
        print("success")
    elif param["order"] == "change":
        param["path"] = up.unquote_plus(param["path"])
        data = param["path"].split(", ")
        data = [i.replace(",", "") for i in data]
        converted = data.copy()
        converted.insert(0, converted.pop())
        dirname = os.path.dirname(converted[0])
        renames = [(data[i], 
                    f"{dirname}/{i}.jpg", 
                    converted[i]) for i in range(len(data))]
        for origin, inside, _ in renames:
            os.rename(origin, inside)
        for _, inside, new in renames:
            os.rename(inside, new)
        print("success")
    with open("time.txt", "a") as file:
        file.write(f"Action {param['order']} lasted {time.time() - start} seconds.")
        file.write("\n")
except Exception as e:
    logging.error("{} in file action.".format(traceback.format_exc()))
    print(traceback.format_exc())
